<?php

namespace Tests\Feature\Customers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomersShowActionTest extends TestCase
{
    use RefreshDatabase;

    private User $systemOperator;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->customer = factory(Customer::class)->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson("/organization/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Sanctum::actingAs($user);

        $this->getJson("/organization/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_shows_a_customer(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->getJson("/organization/customers/{$this->customer->id}")
            ->assertSuccessful()
            ->assertExactJson([
                'data' => [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'vat_number' => $this->customer->vat_number,
                    'address' => $this->customer->address,
                    'logo' => $this->customer->logo,
                    'country' => $this->customer->country,
                    'created_at' => $this->customer->created_at,
                    'updated_at' => $this->customer->updated_at,
                    'deleted_at' => $this->customer->deleted_at,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_show_non_existent_customers(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->getJson('/organization/customers/2')
            ->assertNotFound();
    }

    /** @test */
    public function it_shows_deleted_customers(): void
    {
        $this->customer->delete();

        Sanctum::actingAs($this->systemOperator);

        $this->getJson("/organization/customers/{$this->customer->id}")
            ->assertSuccessful();
    }
}
