<?php

namespace Domains\Users\Tests\Feature\Controllers\Roles;

use Domains\Roles\Models\Role;
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
        $userToHandle = factory(User::class)
            ->state('user')
            ->create();

        $this->getJson("/users/{$userToHandle->id}/roles")
            ->assertUnauthorized();
    }

    /** @test */
    public function it_lists_all_resources_attached_to_a_user(): void
    {
        $userToHandle = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        $role = factory(Role::class)->create([
            'customer_id' => $userToHandle->customer_id,
        ]);

        $userToHandle->roles()->sync(
            $role->id,
        );

        Passport::actingAs($this->user);

        $this->getJson("/users/{$userToHandle->id}/roles")
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
    public function it_lists_all_resources_attached_to_a_system_operator(): void
    {
        $systemOperatorToHandle = factory(User::class)
            ->state('system-operator')
            ->create();

        $role = factory(Role::class)->create([
            'customer_id' => $systemOperatorToHandle->customer_id,
        ]);

        $systemOperatorToHandle->roles()->sync(
            $role->id,
        );

        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/{$systemOperatorToHandle->id}/roles")
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
    public function it_fails_listing_resources_attaches_to_other_customers_users(): void
    {
        $anotherCustomerUser = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($this->user);

        $this->getJson("/users/{$anotherCustomerUser->id}/roles")
            ->assertNotFound();
    }
}
