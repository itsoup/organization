<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Customers\Models\Customer;
use Domains\Roles\Models\Role;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CustomersDeleteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $systemOperator;
    private Customer $customer;
    private Role $role;

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
        $this->deleteJson("/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_delete_resource(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($user);

        $this->deleteJson("/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_deletes_resource(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->deleteJson("/customers/{$this->customer->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('customers', [
            'id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function it_fails_to_delete_non_existent_resources(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->deleteJson('/customers/2')
            ->assertNotFound();
    }
}
