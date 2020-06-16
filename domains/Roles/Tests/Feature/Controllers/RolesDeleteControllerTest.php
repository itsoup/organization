<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Roles\Models\Role;
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
        $this->deleteJson("/roles/{$this->role->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function it_deletes_resources(): void
    {
        Passport::actingAs($this->user);

        $this->deleteJson("/roles/{$this->role->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('roles', [
            'id' => $this->role->id,
        ]);
    }

    /** @test */
    public function it_fails_delete_of_non_existent_resources(): void
    {
        Passport::actingAs($this->user);

        $this->deleteJson('/roles/3')
            ->assertNotFound();
    }

    /** @test */
    public function it_fails_delete_of_other_customers_resources(): void
    {
        $anotherCustomerRole = factory(Role::class)->create();

        Passport::actingAs($this->user);

        $this->deleteJson("/roles/{$anotherCustomerRole->id}")
            ->assertNotFound();
    }
}
