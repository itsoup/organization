<?php

namespace Tests\Feature\Users;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersStoreActionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->user = factory(User::class)
            ->state('user')
            ->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->postJson('/organization/users')
            ->assertUnauthorized();
    }

    /** @test */
    public function system_operators_can_create_new_system_operators(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'customer_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];

        $this->postJson('/organization/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'customer_id' => null,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        /** @var User $newUser */
        $newUser = User::email($payload['email'])->first();

        $this->assertTrue(
            Hash::check($payload['password'], $newUser->password)
        );

        $this->assertTrue($newUser->isSystemOperator());
    }

    /** @test */
    public function users_cant_create_new_system_operators(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Sanctum::actingAs($user);

        $payload = [
            'customer_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];

        $this->postJson('/organization/users', $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'customer_id' => null,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);
    }

    /** @test */
    public function system_operators_can_create_new_users_associated_with_any_customer(): void
    {
        $customer = factory(Customer::class)->create();

        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
            'customer_id' => $customer->id,
        ];

        $this->postJson('/organization/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'customer_id' => $customer->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        /** @var User $newUser */
        $newUser = User::email($payload['email'])->first();

        $this->assertTrue(
            Hash::check($payload['password'], $newUser->password)
        );

        $this->assertTrue($newUser->isUser());
    }

    /** @test */
    public function users_can_create_new_users(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];

        $this->postJson('/organization/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'customer_id' => $this->user->customer_id,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        /** @var User $newUser */
        $newUser = User::email($payload['email'])->first();

        $this->assertTrue(
            Hash::check($payload['password'], $newUser->password)
        );

        $this->assertTrue($newUser->isUser());
    }

    /** @test */
    public function it_fails_if_user_sends_customer_id_when_creating_other_users(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'customer_id' => 1,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];

        $this->postJson('/organization/users', $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->postJson('/organization/users')
            ->assertJsonValidationErrors([
                'name', 'email', 'password',
            ]);
    }

    /** @test */
    public function it_fails_if_email_is_already_registered_to_another_user(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->systemOperator->email,
            'password' => 'password',
        ];

        $this->postJson('/organization/users', $payload)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_if_vat_number_is_already_registered_to_another_user(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'vat_number' => $this->systemOperator->vat_number,
            'password' => 'password',
        ];

        $this->postJson('/organization/users', $payload)
            ->assertJsonValidationErrors(['vat_number']);
    }

    /** @test */
    public function it_fails_if_customer_id_doesnt_exists(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
            'customer_id' => 5,
        ];

        $this->postJson('/organization/users', $payload)
            ->assertJsonValidationErrors(['customer_id']);
    }
}
