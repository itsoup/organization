<?php

namespace Tests\Feature\Customers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomersDeleteActionTest extends TestCase
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
        $this->deleteJson("/organization/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/organization/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_deletes_customer(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->deleteJson("/organization/customers/{$this->customer->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('customers', [
            'id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function it_fails_to_delete_non_existent_customers(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->deleteJson('/organization/customers/2')
            ->assertNotFound();
    }
}
