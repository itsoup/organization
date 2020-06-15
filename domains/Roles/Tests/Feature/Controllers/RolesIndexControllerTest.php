<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Roles\Models\Role;
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

        $this->user = factory(User::class)
            ->state('user')
            ->create();

        factory(Role::class)->create([
            'customer_id' => $this->user->customer_id,
        ]);
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson('/roles')
            ->assertUnauthorized();
    }

    /** @test */
    public function it_lists_non_deleted_resources(): void
    {
        $deletedResource = factory(Role::class)
            ->state('deleted')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user);

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
                    ]
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
        $otherCompanyResource = factory(Role::class)->create();

        Passport::actingAs($this->user);

        $this->getJson('/roles')
            ->assertOk()
            ->assertJsonMissing([
                'id' => $otherCompanyResource->id,
            ]);
    }

    /** @test */
    public function it_lists_resources_related_with_system_operators_customer_id(): void
    {
        $systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $role = factory(Role::class)->create([
            'customer_id' => null
        ]);

        Passport::actingAs($systemOperator);

        $this->getJson('/roles')
            ->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'id' => $role->id,
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_lists_deleted_resources_if_requested(): void
    {
        $deletedResource = factory(Role::class)
            ->state('deleted')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user);

        $this->getJson('/roles?deleted=true')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $deletedResource->id,
            ]);
    }

    /** @test */
    public function it_includes_customer_information_if_requested(): void
    {
        Passport::actingAs($this->user);

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
}
