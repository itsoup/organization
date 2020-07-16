<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Roles\Models\Role;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesShowControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)
            ->state('user')
            ->create();

        $this->role = factory(Role::class)->create([
            'customer_id' => $this->user->customer_id,
        ]);
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson("/roles/{$this->role->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_show_resources(): void
    {
        Passport::actingAs($this->user);

        $this->getJson("/roles/{$this->role->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_shows_a_resource(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson("/roles/{$this->role->id}")
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
                    'id' => $this->role->id,
                    'customer_id' => $this->role->customer_id,
                    'name' => $this->role->name,
                    'scopes' => $this->role->scopes,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_show_non_existent_resources(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson('/roles/3')
            ->assertNotFound();
    }

    /** @test */
    public function it_fails_to_show_resources_related_to_other_customers(): void
    {
        $anotherCustomerRole = factory(Role::class)->create();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson("/roles/{$anotherCustomerRole->id}")
            ->assertNotFound();
    }

    /** @test */
    public function it_includes_customer_information_if_requested(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson("/roles/{$this->role->id}?include=customer")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'customer' => [
                        'id' => $this->role->customer_id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_shows_deleted_resources(): void
    {
        $this->role->delete();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->getJson("/roles/{$this->role->id}")
            ->assertOk();
    }
}
