<?php

namespace Domains\Users\Tests\Feature\Controllers\Users;

use Domains\Users\Database\Factories\UserFactory;
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

        $this->systemOperator = UserFactory::new()->systemOperator()->create();

        $this->user = UserFactory::new()->user()->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson("/users/{$this->user->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_view_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson("/users/{$this->user->id}")
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_view_any_resource(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

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
                    'email_verified_at' => $this->user->email_verified_at,
                ],
            ]);
    }

    /** @test */
    public function it_includes_customer_information_if_requested(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$this->user->id}?include=customer")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'customer' => [
                        'id' => $this->user->customer_id,
                    ],
                ],
            ]);
    }

    /** @test */
    public function system_operators_can_view_other_system_operators(): void
    {
        $anotherSystemOperator = UserFactory::new()->systemOperator()->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$anotherSystemOperator->id}")
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $anotherSystemOperator->id,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_view_non_existent_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson('/users/3')
            ->assertNotFound();
    }

    /** @test */
    public function users_can_view_resources_of_their_customer(): void
    {
        $userFromSameCustomer = UserFactory::new()->user()->create([
            'customer_id' => $this->user->customer_id,
        ]);

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$userFromSameCustomer->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'email_verified_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $userFromSameCustomer->id,
                    'customer_id' => $userFromSameCustomer->customer_id,
                    'vat_number' => $userFromSameCustomer->vat_number,
                    'name' => $userFromSameCustomer->name,
                    'email' => $userFromSameCustomer->email,
                    'phone' => $userFromSameCustomer->phone,
                ],
            ]);
    }

    /** @test */
    public function users_cant_view_resources_of_other_customers(): void
    {
        $userFromAnotherCustomer = UserFactory::new()->user()->create();

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$userFromAnotherCustomer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_view_deleted_resources(): void
    {
        $this->user->delete();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$this->user->id}")
            ->assertOk();
    }

    /** @test */
    public function users_can_view_deleted_resources(): void
    {
        $deletedUser = UserFactory::new()->user()->deleted()->create([
            'customer_id' => $this->user->customer_id,
        ]);

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson("/users/{$deletedUser->id}")
            ->assertOk();
    }
}
