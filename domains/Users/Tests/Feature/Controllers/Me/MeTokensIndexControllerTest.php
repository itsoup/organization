<?php

namespace Domains\Users\Tests\Feature\Controllers\Me;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Tests\TestCase;

class MeTokensIndexControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Client $passportClient;
    private User $user;
    private Token $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportClient = Client::forceCreate([
            'name' => $this->faker->company,
            'secret' => Str::random(40),
            'redirect' => $this->faker->url,
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        $this->user = factory(User::class)
            ->state('user')
            ->create();

        $this->token = $this->user->tokens()->create([
            'id' => Str::random(100),
            'user_id' => $this->user->id,
            'client_id' => $this->passportClient->id,
            'revoked' => false,
            'name' => '',
            'scopes' => [],
        ]);
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson('/me/tokens')
            ->assertUnauthorized();
    }

    /** @test */
    public function it_lists_authenticated_user_tokens(): void
    {
        Passport::actingAs($this->user);

        $this->getJson('/me/tokens')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'created_at',
                        'updated_at',
                        'expires_at',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->token->id,
                        'name' => $this->token->name,
                        'user_id' => (string) $this->token->user_id,
                        'client_id' => (string) $this->token->client_id,
                        'revoked' => $this->token->revoked,
                        'scopes' => $this->token->scopes,
                    ],
                ],
            ]);
    }
}
