<?php

namespace Domains\Users\Tests\Feature\Controllers;

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
    public function system_operators_can_delete_other_system_operators(): void
    {
        $anotherSystemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        Passport::actingAs($this->systemOperator);

        $this->deleteJson("/users/{$anotherSystemOperator->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', [
            'id' => $anotherSystemOperator->id,
        ]);
    }

    /** @test */
    public function system_operators_can_delete_other_users(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->deleteJson("/users/{$this->user->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_fails_to_delete_non_existent_users(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->deleteJson('/users/5')
            ->assertNotFound();
    }
}
