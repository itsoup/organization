<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersLoginActionTest extends TestCase
{
    use RefreshDatabase;

    private User $systemOperator;
    private User $user;

    public function validClientNameProvider(): array
    {
        return [
            'Web Client' => ['web'],
            'Mobile Client' => ['mobile'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create([
                'password' => 'password',
            ]);

        $this->user = factory(User::class)
            ->state('user')
            ->create([
                'password' => 'password',
            ]);
    }

    /**
     * @test
     * @dataProvider validClientNameProvider
     *
     * @param string $clientName
     */
    public function system_operators_with_valid_credentials_can_login(string $clientName): void
    {
        $this->postJson(
            '/login',
            [
                'email' => $this->systemOperator->email,
                'password' => 'password',
                'client_name' => $clientName,
            ],
        )
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'access_token', 'client_name', 'abilities', 'created_at',
                ],
            ]);

        $this->assertCount(1, $this->systemOperator->tokens);
        $this->assertEquals($clientName, $this->systemOperator->tokens->first()->name);
    }

    /**
     * @test
     * @dataProvider validClientNameProvider
     *
     * @param string $clientName
     */
    public function users_with_valid_credentials_can_login(string $clientName): void
    {
        $this->postJson(
            '/login',
            [
                'email' => $this->user->email,
                'password' => 'password',
                'client_name' => $clientName,
            ],
        )
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'access_token', 'client_name', 'abilities', 'created_at',
                ],
            ]);

        $this->assertCount(1, $this->user->tokens);
        $this->assertEquals($clientName, $this->user->tokens->first()->name);
    }

    /**
     * @test
     * @dataProvider validClientNameProvider
     *
     * @param string $clientName
     */
    public function guests_with_invalid_credentials_cant_login(string $clientName): void
    {
        // test wrong email
        $this->postJson(
            '/login',
            [
                'email' => 'bad@email.tld',
                'password' => 'password',
                'client_name' => $clientName,
            ],
        )
            ->assertJsonValidationErrors([
                'email',
            ]);

        // test wrong password
        $this->postJson(
            '/login',
            [
                'email' => $this->user->email,
                'password' => 'wrong_password',
                'client_name' => $clientName,
            ],
        )
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        $this->postJson('/login')
            ->assertJsonValidationErrors([
                'email', 'password', 'client_name',
            ]);
    }

    /** @test */
    public function authenticated_users_cant_login_again(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->postJson('/login')
            ->assertForbidden();

        Sanctum::actingAs($this->user);

        $this->postJson('/login')
            ->assertForbidden();
    }
}
