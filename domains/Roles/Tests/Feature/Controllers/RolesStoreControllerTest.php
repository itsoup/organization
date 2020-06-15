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
    public function it_fails_to_store_resource_if_missing_required_input(): void
    {
        Passport::actingAs($this->user);

        $this->postJson('/roles')
            ->assertJsonValidationErrors([
                'name', 'scopes',
            ]);
    }

    /** @test */
    public function users_can_store_new_resources(): void
    {
        Passport::actingAs($this->user);

        $attributes = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:roles:view',
                'organization:roles:manage',
            ],
        ];

        $this->postJson('/roles', $attributes)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'customer_id' => $this->user->customer_id,
            'name' => $attributes['name'],
            'scopes' => json_encode($attributes['scopes']),
        ]);
    }

    /** @test */
    public function system_operators_can_store_new_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $attributes = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:roles:view',
                'organization:roles:manage',
            ],
        ];

        $this->postJson('/roles', $attributes)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'customer_id' => $this->systemOperator->customer_id,
            'name' => $attributes['name'],
            'scopes' => json_encode($attributes['scopes']),
        ]);
    }
}
