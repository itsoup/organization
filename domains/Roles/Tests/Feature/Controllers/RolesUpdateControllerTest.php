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

        $this->user = factory(User::class)->create();

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
    public function it_fails_update_if_missing_required_input(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson("/roles/{$this->role->id}")
            ->assertJsonValidationErrors([
                'name', 'scopes',
            ]);
    }

    /** @test */
    public function it_updates_a_resource(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'scopes' => [
                'organization:roles:view',
            ],
        ];

        Passport::actingAs($this->user);

        $this->patchJson("/roles/{$this->role->id}", $attributes)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'id' => $this->role->id,
            'customer_id' => $this->user->customer_id,
            'name' => $attributes['name'],
            'scopes' => json_encode($attributes['scopes']),
        ]);
    }

    /** @test */
    public function it_fails_to_update_non_existent_resources(): void
    {
        $attributes = [
            'name' => $this->faker->word,
            'scopes' => [
                'organization:roles:view',
            ],
        ];

        Passport::actingAs($this->user);

        $this->patchJson("/roles/3", $attributes)
            ->assertNotFound();
    }
}
