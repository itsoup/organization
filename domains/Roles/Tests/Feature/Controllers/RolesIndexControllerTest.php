<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Roles\Database\Factories\RoleFactory;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->user()->create();

        RoleFactory::new([
            'customer_id' => $this->user->customer_id,
        ])->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson('/roles')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_list_resources(): void
    {
        Passport::actingAs($this->user);

        $this->getJson('/roles')
            ->assertForbidden();
    }

    /** @test */
    public function it_lists_non_deleted_resources(): void
    {
        $deletedResource = RoleFactory::new([
            'customer_id' => $this->user->customer_id,
        ])
            ->deleted()
            ->create();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'customer_id',
                        'name',
                        'scopes',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                    ],
                ],
                'links' => [
                    'first',
                    'prev',
                    'next',
                    'last',
                ],
            ])
            ->assertJsonMissing([
                'id' => $deletedResource->id,
            ]);
    }

    /** @test */
    public function it_lists_resources_related_with_user_customer_id(): void
    {
        $otherCompanyResource = RoleFactory::new()->create();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles')
            ->assertOk()
            ->assertJsonMissing([
                'id' => $otherCompanyResource->id,
            ]);
    }

    /** @test */
    public function it_lists_resources_related_with_system_operators_customer_id(): void
    {
        $systemOperator = UserFactory::new()->systemOperator()->create();

        $role = RoleFactory::new([
            'customer_id' => null,
        ])->create();

        Passport::actingAs($systemOperator, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles')
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $role->id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_lists_deleted_resources_if_requested(): void
    {
        $deletedResource = RoleFactory::new([
            'customer_id' => $this->user->customer_id,
        ])
            ->deleted()
            ->create();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles?deleted=true')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $deletedResource->id,
            ]);
    }

    /** @test */
    public function it_includes_customer_information_if_requested(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles?include=customer')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'customer',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_navigates_to_next_page(): void
    {
        RoleFactory::times(15)->create();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles?page=2')
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                ],
            ]);
    }
}
