<?php

namespace Tests\Feature\Customers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomersIndexActionTest extends TestCase
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
        $this->getJson('/organization/customers')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Sanctum::actingAs($user);

        $this->getJson('/organization/customers')
            ->assertForbidden();
    }

    /** @test */
    public function it_lists_non_deleted_customers(): void
    {
        $deletedCustomer = factory(Customer::class)->state('deleted')->create();

        Sanctum::actingAs($this->systemOperator);

        $this->getJson('/organization/customers')
            ->assertSuccessful()
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

        Sanctum::actingAs($this->systemOperator);

        $this->getJson('/organization/customers?deleted=true')
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $deletedCustomer->id,
            ]);
    }

    /** @test */
    public function it_navigates_to_next_page(): void
    {
        factory(Customer::class, 15)->create();

        Sanctum::actingAs($this->systemOperator);

        $this->getJson('/organization/customers?page=2')
            ->assertSuccessful()
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                ],
            ]);
    }
}
