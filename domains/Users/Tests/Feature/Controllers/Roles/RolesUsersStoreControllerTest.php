<?php

namespace Domains\Users\Tests\Feature\Controllers\Roles;

use Domains\Roles\Models\Role;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesUsersStoreControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $systemOperator;
    private User $user;
    private User $userToHandle;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->user = factory(User::class)
            ->state('user')
            ->create();

        $this->userToHandle = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        $this->role = factory(Role::class)->create([
            'customer_id' => $this->user->customer_id,
        ]);
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $this->putJson("/users/{$this->userToHandle->id}/roles")
            ->assertUnauthorized();
    }

    /** @test */
    public function it_fails_if_required_input_is_missing(): void
    {
        Passport::actingAs($this->user);

        $this->putJson("/users/{$this->userToHandle->id}/roles")
            ->assertJsonValidationErrors([
                'roles',
            ]);
    }

    /** @test */
    public function it_attaches_roles_to_users(): void
    {
        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user);

        $this->putJson("/users/{$this->userToHandle->id}/roles", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $this->userToHandle->id,
            'role_id' => $this->role->id,
        ]);
    }

    /** @test */
    public function it_attaches_roles_to_system_operators(): void
    {
        $systemOperatorToHandle = factory(User::class)
            ->state('system-operator')
            ->create();

        $role = factory(Role::class)->create([
            'customer_id' => $systemOperatorToHandle->customer_id,
        ]);

        $payload = [
            'roles' => [
                $role->id,
            ],
        ];

        Passport::actingAs($this->systemOperator);

        $this->putJson("/users/{$systemOperatorToHandle->id}/roles", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $systemOperatorToHandle->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_cant_attach_roles_to_other_customers_users(): void
    {
        $otherCustomerUser = factory(User::class)
            ->state('user')
            ->create();

        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user);

        $this->putJson("/users/{$otherCustomerUser->id}/roles", $payload)
            ->assertNotFound();
    }

    /** @test */
    public function it_cant_attach_roles_to_non_existent_users(): void
    {
        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user);

        $this->putJson('/users/4/roles', $payload)
            ->assertNotFound();
    }

    /** @test */
    public function it_cant_attach_other_customers_roles_to_users(): void
    {
        $payload = [
            'roles' => [
                factory(Role::class)->create()->id,
            ],
        ];

        Passport::actingAs($this->user);

        $this->putJson("/users/{$this->userToHandle->id}/roles", $payload)
            ->assertJsonValidationErrors([
                'roles.0',
            ]);
    }

    /** @test */
    public function it_cant_attach_roles_to_authenticated_user(): void
    {
        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user);

        $this->putJson("/users/{$this->user->id}/roles", $payload)
            ->assertForbidden();
    }
}
