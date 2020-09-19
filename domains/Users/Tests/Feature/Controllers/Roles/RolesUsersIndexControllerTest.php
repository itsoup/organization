<?php

namespace Domains\Users\Tests\Feature\Controllers\Roles;

use Domains\Roles\Database\Factories\RoleFactory;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesUsersIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $systemOperator;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        $this->user = UserFactory::new()->user()->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $userToHandle = UserFactory::new()->user()->create();

        $this->getJson("/users/{$userToHandle->id}/roles")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $userToHandle = UserFactory::new()
            ->user()
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user);

        $this->getJson("/users/{$userToHandle->id}/roles")
            ->assertForbidden();
    }

    /** @test */
    public function it_lists_all_resources_attached_to_a_user(): void
    {
        $userToHandle = UserFactory::new()
            ->user()
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        $role = RoleFactory::new([
            'customer_id' => $userToHandle->customer_id,
        ])->create();

        $userToHandle->roles()->sync(
            $role->id,
        );

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$userToHandle->id}/roles")
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
    public function it_lists_all_resources_attached_to_a_deleted_user(): void
    {
        $userToHandle = UserFactory::new()
            ->user()
            ->deleted()
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        $role = RoleFactory::new([
            'customer_id' => $userToHandle->customer_id,
        ])->create();

        $userToHandle->roles()->sync(
            $role->id,
        );

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$userToHandle->id}/roles")
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
    public function it_lists_all_resources_attached_to_a_system_operator(): void
    {
        $systemOperatorToHandle = UserFactory::new()->systemOperator()->create();

        $role = RoleFactory::new([
            'customer_id' => $systemOperatorToHandle->customer_id,
        ])->create();

        $systemOperatorToHandle->roles()->sync(
            $role->id,
        );

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$systemOperatorToHandle->id}/roles")
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
    public function it_lists_all_resources_attached_to_a_deleted_system_operator(): void
    {
        $systemOperatorToHandle = UserFactory::new()->systemOperator()->deleted()->create();

        $role = RoleFactory::new([
            'customer_id' => $systemOperatorToHandle->customer_id,
        ])->create();

        $systemOperatorToHandle->roles()->sync(
            $role->id,
        );

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$systemOperatorToHandle->id}/roles")
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
    public function it_fails_listing_resources_attaches_to_other_customers_users(): void
    {
        $anotherCustomerUser = UserFactory::new()->user()->create();

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$anotherCustomerUser->id}/roles")
            ->assertNotFound();
    }
}
