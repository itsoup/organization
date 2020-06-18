<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CustomersShowControllerTest extends TestCase
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
        $this->getJson("/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($user);

        $this->getJson("/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_shows_a_customer(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson("/customers/{$this->customer->id}")
            ->assertOk()
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
        Passport::actingAs($this->systemOperator);

        $this->getJson('/customers/2')
            ->assertNotFound();
    }

    /** @test */
    public function it_shows_deleted_customers(): void
    {
        $this->customer->delete();

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson("/customers/{$this->customer->id}")
            ->assertOk();
    }
}
