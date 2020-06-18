<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CustomersIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $systemOperator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        factory(Customer::class, 5)->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson('/customers')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($user);

        $this->getJson('/customers')
            ->assertForbidden();
    }

    /** @test */
    public function it_lists_non_deleted_customers(): void
    {
        $deletedCustomer = factory(Customer::class)->state('deleted')->create();

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson('/customers')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'vat_number',
                        'address',
                        'logo',
                        'country',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                ],
                'links' => [
                    'first', 'prev', 'next', 'last',
                ],
            ])
            ->assertJsonMissing([
                'id' => $deletedCustomer->id,
            ]);
    }

    /** @test */
    public function it_lists_deleted_customers_if_requested(): void
    {
        $deletedCustomer = factory(Customer::class)->state('deleted')->create();

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson('/customers?deleted=true')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $deletedCustomer->id,
            ]);
    }

    /** @test */
    public function it_navigates_to_next_page(): void
    {
        factory(Customer::class, 15)->create();

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson('/customers?page=2')
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                ],
            ]);
    }
}
