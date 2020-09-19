<?php

namespace Domains\Users\Tests\Feature\Controllers\Roles;

use Domains\Roles\Database\Factories\RoleFactory;
use Domains\Roles\Models\Role;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Tests\TestCase;

class RolesUsersStoreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;
    private User $userToHandle;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        $this->user = UserFactory::new()->user()->create();

        $this->userToHandle = UserFactory::new()->user()->create([
            'customer_id' => $this->user->customer_id,
        ]);

        $this->role = RoleFactory::new([
            'customer_id' => $this->user->customer_id,
        ])->create();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $this->putJson("/users/{$this->userToHandle->id}/roles")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_attach_roles_to_users(): void
    {
        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user);

        $this->putJson("/users/{$this->userToHandle->id}/roles", $payload)
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_required_input_is_missing(): void
    {
        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

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

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->putJson("/users/{$this->userToHandle->id}/roles", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $this->userToHandle->id,
            'role_id' => $this->role->id,
        ]);
    }

    /** @test */
    public function it_attaches_roles_to_deleted_users(): void
    {
        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->userToHandle->delete();

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
        $systemOperatorToHandle = UserFactory::new()->systemOperator()->create();

        $role = RoleFactory::new([
            'customer_id' => $systemOperatorToHandle->customer_id,
        ])->create();

        $payload = [
            'roles' => [
                $role->id,
            ],
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->putJson("/users/{$systemOperatorToHandle->id}/roles", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $systemOperatorToHandle->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    public function it_attaches_roles_to_deleted_system_operators(): void
    {
        $systemOperatorToHandle = UserFactory::new()->systemOperator()->deleted()->create();

        $role = RoleFactory::new([
            'customer_id' => $systemOperatorToHandle->customer_id,
        ])->create();

        $payload = [
            'roles' => [
                $role->id,
            ],
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

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
        $otherCustomerUser = UserFactory::new()->user()->create();

        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

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

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->putJson('/users/4/roles', $payload)
            ->assertNotFound();
    }

    /** @test */
    public function it_cant_attach_other_customers_roles_to_users(): void
    {
        $payload = [
            'roles' => [
                RoleFactory::new()->create()->id,
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

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

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->putJson("/users/{$this->user->id}/roles", $payload)
            ->assertForbidden();
    }

    /** @test */
    public function it_invalidates_all_pre_existent_tokens_when_a_role_is_attached(): void
    {
        /** @var Token $token */
        $preExistentToken = $this->userToHandle->tokens()->create([
            'id' => $this->faker->sha1,
            'client_id' => 1,
            'revoked' => 0,
        ]);

        $payload = [
            'roles' => [
                $this->role->id,
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->putJson("/users/{$this->userToHandle->id}/roles", $payload)
            ->assertNoContent();

        self::assertTrue($preExistentToken->fresh()->revoked);
    }
}
