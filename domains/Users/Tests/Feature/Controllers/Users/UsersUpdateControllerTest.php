<?php

namespace Domains\Users\Tests\Feature\Controllers\Users;

use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersUpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;
    private User $userToUpdate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        $this->user = UserFactory::new()->user()->create();

        $this->userToUpdate = UserFactory::new()->user()->create([
            'customer_id' => $this->user->customer_id,
        ]);
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->patchJson("/users/{$this->userToUpdate->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_update_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->patchJson("/users/{$this->userToUpdate->id}")
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_update_any_resource(): void
    {
        $anotherCustomer = CustomerFactory::new()->create();

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
            'customer_id' => $anotherCustomer->id,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', $payload);
    }

    /** @test */
    public function users_can_update_resources_related_with_their_customer(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', $payload);
    }

    /** @test */
    public function users_cant_update_resources_from_other_customers(): void
    {
        $userFromAnotherCustomer = UserFactory::new()->user()->create();

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$userFromAnotherCustomer->id}", $payload)
            ->assertNotFound();
    }

    /** @test */
    public function users_cant_update_resources_customer_id(): void
    {
        $anotherCustomer = CustomerFactory::new()->create();

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => $anotherCustomer->id,
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_update_resources_customer_id_to_null(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => null,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', $payload);
    }

    /** @test */
    public function users_cant_update_resources_customer_id_to_null(): void
    {
        $anotherUser = UserFactory::new()->user()->create([
            'customer_id' => $this->userToUpdate->customer_id,
        ]);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => null,
        ];

        Passport::actingAs($anotherUser, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}")
            ->assertJsonValidationErrors([
                'name',
            ]);
    }

    /** @test */
    public function it_fails_if_email_is_already_registered_to_another_resource(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->systemOperator->email,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_if_email_is_sent_empty(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => '',
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_updates_resource_ignoring_its_email_uniqueness(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->userToUpdate->email,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();
    }

    /** @test */
    public function it_updates_resource_ignoring_its_vat_number_uniqueness(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'vat_number' => $this->userToUpdate->vat_number,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();
    }

    /** @test */
    public function it_fails_if_vat_number_is_already_registered_to_another_resource(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'vat_number' => $this->systemOperator->vat_number,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertJsonValidationErrors(['vat_number']);
    }

    /** @test */
    public function it_fails_if_customer_id_doesnt_exists(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => 5,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertJsonValidationErrors(['customer_id']);
    }

    /** @test */
    public function it_fails_if_is_self_account(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->systemOperator->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_user_sends_customer_id_when_updating_resources(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
            'customer_id' => 1,
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->patchJson("/users/{$this->userToUpdate->id}", $payload)
            ->assertForbidden();
    }
}
