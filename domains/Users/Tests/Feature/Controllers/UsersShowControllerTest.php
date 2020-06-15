<?php

namespace Domains\Users\Tests\Feature\Controllers;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersShowControllerTest extends TestCase
{
    use RefreshDatabase;

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
    public function system_operators_can_view_details_of_all_users(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/{$this->user->id}")
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $this->user->id,
                    'customer_id' => $this->user->customer_id,
                    'vat_number' => $this->user->vat_number,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'created_at' => $this->user->created_at,
                    'updated_at' => $this->user->updated_at,
                    'deleted_at' => $this->user->deleted_at,
                ]
            ]);
    }

    /** @test */
    public function system_operators_can_view_details_of_other_system_operators(): void
    {
        $anotherSystemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/{$anotherSystemOperator->id}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $anotherSystemOperator->id,
                ]
            ]);
    }

    /** @test */
    public function it_fails_to_show_non_existent_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson('/users/3')
            ->assertNotFound();
    }

    /** @test */
    public function users_can_view_details_of_users_of_their_customer(): void
    {
        $userFromSameCustomer = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user);

        $this->getJson("/users/{$userFromSameCustomer->id}")
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $userFromSameCustomer->id,
                    'customer_id' => $userFromSameCustomer->customer_id,
                    'vat_number' => $userFromSameCustomer->vat_number,
                    'name' => $userFromSameCustomer->name,
                    'email' => $userFromSameCustomer->email,
                    'phone' => $userFromSameCustomer->phone,
                    'created_at' => $userFromSameCustomer->created_at,
                    'updated_at' => $userFromSameCustomer->updated_at,
                    'deleted_at' => $userFromSameCustomer->deleted_at,
                ]
            ]);
    }

    /** @test */
    public function users_cant_view_details_of_users_of_other_customers(): void
    {
        $userFromAnotherCustomer = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($this->user);

        $this->getJson("/users/{$userFromAnotherCustomer->id}")
            ->assertForbidden();
    }
}