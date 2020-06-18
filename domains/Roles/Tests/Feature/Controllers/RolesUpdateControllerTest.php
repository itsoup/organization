<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Roles\Models\Role;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesUpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
        $this->patchJson("/roles/{$this->role->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_update_resources(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson("/roles/{$this->role->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_update_if_missing_required_input(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson("/roles/{$this->role->id}")
            ->assertJsonValidationErrors([
                'name', 'scopes',
            ]);
    }

    /** @test */
    public function it_updates_a_resource(): void
    {
        $payload = [
            'name' => $this->faker->word,
            'scopes' => [
                'organization:roles:view',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson("/roles/{$this->role->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'id' => $this->role->id,
            'customer_id' => $this->user->customer_id,
            'name' => $payload['name'],
            'scopes' => json_encode($payload['scopes']),
        ]);
    }

    /** @test */
    public function it_fails_to_update_non_existent_resources(): void
    {
        $payload = [
            'name' => $this->faker->word,
            'scopes' => [
                'organization:roles:view',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson('/roles/3', $payload)
            ->assertNotFound();
    }

    /** @test */
    public function it_fails_to_update_resources_of_other_customers(): void
    {
        $otherCustomerRole = factory(Role::class)->create();

        $payload = [
            'name' => $this->faker->word,
            'scopes' => [
                'organization:roles:view',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson("/roles/{$otherCustomerRole->id}", $payload)
            ->assertNotFound();
    }

    /** @test */
    public function users_cant_update_resources_with_scopes_for_customers_module(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson("/roles/{$this->role->id}", $payload)
            ->assertJsonValidationErrors([
                'scopes.0', 'scopes.1',
            ]);
    }

    /** @test */
    public function system_operators_can_update_resources_with_scopes_for_customers_module(): void
    {
        $systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $role = factory(Role::class)->create([
            'customer_id' => $systemOperator->customer_id,
        ]);

        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
            ],
        ];

        Passport::actingAs($systemOperator, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson("/roles/{$role->id}", $payload)
            ->assertNoContent();
    }

    /** @test */
    public function it_fails_if_resource_scopes_have_invalid_value(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'invalid:scope:value',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->patchJson("/roles/{$this->role->id}", $payload)
            ->assertJsonValidationErrors([
                'scopes.0',
            ]);
    }
}
