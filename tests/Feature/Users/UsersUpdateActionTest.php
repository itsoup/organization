<?php

namespace Tests\Feature\Users;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersUpdateActionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;
    private User $userToUpdate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->user = factory(User::class)
            ->state('user')
            ->create();

        $this->userToUpdate = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->patchJson("/organization/users/{$this->userToUpdate->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function system_operators_can_update_all_users(): void
    {
        $anotherCustomer = factory(Customer::class)->create();

        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
            'customer_id' => $anotherCustomer->id,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', $payload);
    }

    /** @test */
    public function users_can_update_other_users_related_with_their_customer(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', $payload);
    }

    /** @test */
    public function users_cant_update_customer_id(): void
    {
        $anotherCustomer = factory(Customer::class)->create();

        Sanctum::actingAs($this->user);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => $anotherCustomer->id,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_update_customer_id_to_null(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => null,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', $payload);
    }

    /** @test */
    public function users_cant_update_customer_id_to_null(): void
    {
        $anotherUser = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->userToUpdate->customer_id,
            ]);

        Sanctum::actingAs($anotherUser);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => null,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->patchJson("/organization/users/{$this->userToUpdate->id}")
            ->assertJsonValidationErrors([
                'name', 'email',
            ]);
    }

    /** @test */
    public function it_fails_if_email_is_already_registered_to_another_user(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->systemOperator->email,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
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
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertJsonValidationErrors(['vat_number']);
    }

    /** @test */
    public function it_fails_if_customer_id_doesnt_exists(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'customer_id' => 5,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertJsonValidationErrors(['customer_id']);
    }

    /** @test */
    public function it_fails_if_is_self_account(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->patchJson("/organization/users/{$this->systemOperator->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_user_sends_customer_id_when_updating_other_users(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
            'customer_id' => 1,
        ];

        $this->patchJson("/organization/users/{$this->userToUpdate->id}", $payload)
            ->assertForbidden();
    }
}
