<?php

namespace Domains\Users\Tests\Feature\Controllers\Users;

use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersStoreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        $this->user = UserFactory::new()->user()->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->postJson('/users')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_store_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->postJson('/users')
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_store_new_system_operators(): void
    {
        Notification::fake();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $payload = [
            'customer_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
        ];

        $this->postJson('/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'customer_id' => null,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'vat_number' => $payload['vat_number'],
            'phone' => $payload['phone'],
        ]);

        /** @var User $newUser */
        $newUser = User::email($payload['email'])->first();

        Notification::assertSentTo($newUser, VerifyEmail::class);
        self::assertFalse($newUser->hasVerifiedEmail());

        self::assertTrue(
            Hash::check($payload['password'], $newUser->password)
        );

        self::assertTrue($newUser->isSystemOperator());
    }

    /** @test */
    public function users_cant_store_new_system_operators(): void
    {
        $user = UserFactory::new()->user()->create();

        Passport::actingAs($user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $payload = [
            'customer_id' => null,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];

        $this->postJson('/users', $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'customer_id' => null,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);
    }

    /** @test */
    public function system_operators_can_store_new_users_associated_with_any_customer(): void
    {
        Notification::fake();

        $customer = CustomerFactory::new()->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
            'customer_id' => $customer->id,
        ];

        $this->postJson('/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'customer_id' => $customer->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        /** @var User $newUser */
        $newUser = User::email($payload['email'])->first();

        Notification::assertSentTo($newUser, VerifyEmail::class);
        self::assertFalse($newUser->hasVerifiedEmail());

        self::assertTrue(
            Hash::check($payload['password'], $newUser->password)
        );

        self::assertTrue($newUser->isUser());
    }

    /** @test */
    public function users_can_store_new_users(): void
    {
        Notification::fake();

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->postJson('/users', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'customer_id' => $this->user->customer_id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'vat_number' => $payload['vat_number'],
            'phone' => $payload['phone'],
        ]);

        /** @var User $newUser */
        $newUser = User::email($payload['email'])->first();

        Notification::assertSentTo($newUser, VerifyEmail::class);
        self::assertFalse($newUser->hasVerifiedEmail());

        self::assertTrue(
            Hash::check($payload['password'], $newUser->password)
        );

        self::assertTrue($newUser->isUser());
    }

    /** @test */
    public function it_fails_if_user_sends_customer_id_when_creating_other_users(): void
    {
        $payload = [
            'customer_id' => 1,
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->postJson('/users', $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->postJson('/users')
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }

    /** @test */
    public function it_fails_if_email_is_already_registered_to_another_resource(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->systemOperator->email,
            'password' => 'password',
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->postJson('/users', $payload)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_if_vat_number_is_already_registered_to_another_resource(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'vat_number' => $this->systemOperator->vat_number,
            'password' => 'password',
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->postJson('/users', $payload)
            ->assertJsonValidationErrors(['vat_number']);
    }

    /** @test */
    public function it_fails_if_customer_id_doesnt_exists(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password',
            'customer_id' => 5,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->postJson('/users', $payload)
            ->assertJsonValidationErrors(['customer_id']);
    }
}
