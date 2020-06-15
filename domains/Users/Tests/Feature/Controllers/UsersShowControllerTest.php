<?php

namespace Domains\Users\Tests\Feature\Controllers;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersShowControllerTest extends TestCase
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
    public function system_operators_can_view_details_of_all_users(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/{$this->user->id}")
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    'id' => $this->user->id,
                    'customer_id' => $this->user->customer_id,
                    'vat_number' => $this->user->vat_number,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'created_at' => $this->user->created_at,
                    'updated_at' => $this->user->updated_at,
                    'deleted_at' => $this->user->deleted_at,
                ]
            ]);
    }

    /** @test */
    public function system_operators_can_view_details_of_other_system_operators(): void
    {
        $anotherSystemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/{$anotherSystemOperator->id}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $anotherSystemOperator->id,
                    'customer_id' => $anotherSystemOperator->customer_id,
                    'vat_number' => $anotherSystemOperator->vat_number,
                    'name' => $anotherSystemOperator->name,
                    'email' => $anotherSystemOperator->email,
                    'phone' => $anotherSystemOperator->phone,
                ]
            ]);
    }

    /** @test */
    public function it_fails_to_show_non_existent_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/3")
            ->assertNotFound();
    }
}
