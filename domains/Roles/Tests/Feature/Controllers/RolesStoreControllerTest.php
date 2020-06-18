<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesStoreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->user = factory(User::class)
            ->state('user')
            ->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->postJson('/roles')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_create_resources(): void
    {
        Passport::actingAs($this->user);

        $this->postJson('/roles')
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_missing_required_input(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles')
            ->assertJsonValidationErrors([
                'name', 'scopes',
            ]);
    }

    /** @test */
    public function it_fails_if_scopes_have_invalid_value(): void
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

        $this->postJson('/roles', $payload)
            ->assertJsonValidationErrors([
                'scopes.0',
            ]);
    }

    /** @test */
    public function users_can_store_new_resources_automatically_associated_with_their_customer_id(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:roles:view',
                'organization:roles:manage',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'customer_id' => $this->user->customer_id,
            'name' => $payload['name'],
            'scopes' => json_encode($payload['scopes']),
        ]);
    }

    /** @test */
    public function system_operators_can_store_new_resources_automatically_associated_with_their_customer_id(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:roles:view',
                'organization:roles:manage',
            ],
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'customer_id' => $this->systemOperator->customer_id,
            'name' => $payload['name'],
            'scopes' => json_encode($payload['scopes']),
        ]);
    }

    /** @test */
    public function users_cant_store_resources_with_scopes_for_customers_module(): void
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

        $this->postJson('/roles', $payload)
            ->assertJsonValidationErrors([
                'scopes.0', 'scopes.1',
            ]);
    }

    /** @test */
    public function system_operators_can_store_resources_with_scopes_for_customers_module(): void
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

        Passport::actingAs($this->systemOperator, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertNoContent();
    }
}
