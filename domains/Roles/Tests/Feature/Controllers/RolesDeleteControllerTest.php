<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Roles\Database\Factories\RoleFactory;
use Domains\Roles\Models\Role;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesDeleteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->user()->create();

        $this->role = RoleFactory::new([
            'customer_id' => $this->user->customer_id,
        ])->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->deleteJson("/roles/{$this->role->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_delete_resources(): void
    {
        Passport::actingAs($this->user);

        $this->deleteJson("/roles/{$this->role->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_deletes_resources(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->deleteJson("/roles/{$this->role->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('roles', [
            'id' => $this->role->id,
        ]);
    }

    /** @test */
    public function it_fails_delete_of_non_existent_resources(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->deleteJson('/roles/3')
            ->assertNotFound();
    }

    /** @test */
    public function it_fails_delete_of_other_customers_resources(): void
    {
        $anotherCustomerRole = RoleFactory::new()->create();

        Passport::actingAs($this->user, [
            'organization:roles:view',
        ]);

        $this->deleteJson("/roles/{$anotherCustomerRole->id}")
            ->assertNotFound();
    }
}
