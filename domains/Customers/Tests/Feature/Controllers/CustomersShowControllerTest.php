<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Customers\Models\Customer;
use Domains\Users\Database\Factories\UserFactory;
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

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        $this->customer = CustomerFactory::new()->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson("/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_resource(): void
    {
        $user = UserFactory::new()->user()->create();

        Passport::actingAs($user);

        $this->getJson("/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_shows_a_resource(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson("/customers/{$this->customer->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'vat_number' => $this->customer->vat_number,
                    'address' => $this->customer->address,
                    'logo' => $this->customer->logo,
                    'country' => $this->customer->country,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_show_non_existent_resources(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson('/customers/2')
            ->assertNotFound();
    }

    /** @test */
    public function it_shows_deleted_resources(): void
    {
        $this->customer->delete();

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
        ]);

        $this->getJson("/customers/{$this->customer->id}")
            ->assertOk();
    }
}
