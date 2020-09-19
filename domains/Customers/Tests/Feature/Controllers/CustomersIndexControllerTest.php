<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Users\Database\Factories\UserFactory;
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

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        CustomerFactory::times(5)->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson('/customers')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_resources(): void
    {
        $user = UserFactory::new()->user()->create();

        Passport::actingAs($user);

        $this->getJson('/customers')
            ->assertForbidden();
    }

    /** @test */
    public function it_lists_non_deleted_resources(): void
    {
        $deletedCustomer = CustomerFactory::new()->deleted()->create();

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
    public function it_lists_deleted_resources_if_requested(): void
    {
        $deletedCustomer = CustomerFactory::new()->deleted()->create();

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
        CustomerFactory::times(15)->create();

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
