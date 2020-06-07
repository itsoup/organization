<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsersDeleteActionTest extends TestCase
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
        $this->deleteJson("/organization/users/{$this->user->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function system_operators_can_delete_other_system_operators(): void
    {
        $anotherSystemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        Sanctum::actingAs($this->systemOperator);

        $this->deleteJson("/organization/users/{$anotherSystemOperator->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', [
            'id' => $anotherSystemOperator->id,
        ]);
    }

    /** @test */
    public function system_operators_can_delete_other_users(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->deleteJson("/organization/users/{$this->user->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('users', [
            'id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_fails_to_delete_non_existent_users(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->deleteJson('/organization/users/5')
            ->assertNotFound();
    }
}
