<?php

namespace Domains\Users\Tests\Feature\Controllers\Users;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersDeleteControllerTest extends TestCase
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
        $this->deleteJson("/users/{$this->user->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_system_operators_cant_delete_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->deleteJson("/users/{$this->user->id}")
            ->assertForbidden();
    }

    /** @test */
    public function unauthorized_users_cant_delete_resources(): void
    {
        /** @var User $userToDelete */
        $userToDelete = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user);

        $this->deleteJson("/users/{$userToDelete->id}")
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_delete_other_system_operators(): void
    {
        $anotherSystemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->deleteJson("/users/{$anotherSystemOperator->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', [
            'id' => $anotherSystemOperator->id,
        ]);
    }

    /** @test */
    public function system_operators_can_delete_other_users(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->deleteJson("/users/{$this->user->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_fails_to_delete_non_existent_resources(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->deleteJson('/users/5')
            ->assertNotFound();
    }

    /** @test */
    public function users_can_delete_other_users_related_with_their_customer(): void
    {
        /** @var User $userToDelete */
        $userToDelete = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->deleteJson("users/{$userToDelete->id}")
            ->assertNoContent();

        $this->assertTrue($userToDelete->fresh()->trashed());
    }

    /** @test */
    public function users_cant_delete_other_users_of_other_customers(): void
    {
        /** @var User $userToDelete */
        $userToDelete = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->deleteJson("users/{$userToDelete->id}")
            ->assertForbidden();

        $this->assertFalse($userToDelete->fresh()->trashed());
    }

    /** @test */
    public function users_cant_delete_themselves(): void
    {
        Passport::actingAs($this->user, [
            'organization:users:view',
            'organization:users:manage',
        ]);

        $this->deleteJson("users/{$this->user->id}")
            ->assertForbidden();
    }
}
