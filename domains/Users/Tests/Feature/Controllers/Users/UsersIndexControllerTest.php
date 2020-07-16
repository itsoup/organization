<?php

namespace Domains\Users\Tests\Feature\Controllers\Users;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UsersIndexControllerTest extends TestCase
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
        $this->getJson('/users')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_list_resources(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->getJson('/users')
            ->assertForbidden();
    }

    /** @test */
    public function system_operators_can_list_all_non_deleted_resources(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        $deletedUser = factory(User::class)
            ->states('user', 'deleted')
            ->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson('/users')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'customer_id',
                        'vat_number',
                        'name',
                        'email',
                        'phone',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'email_verified_at',
                    ],
                ],
                'links' => [
                    'first', 'prev', 'next', 'last',
                ],
            ])
            ->assertJsonFragment([
                'id' => $user->id,
            ])
            ->assertJsonMissing([
                'id' => $deletedUser->id,
            ]);
    }

    /** @test */
    public function users_can_list_all_non_deleted_resources_related_with_their_customer(): void
    {
        $otherUser = factory(User::class)
            ->state('user')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        $deletedUser = factory(User::class)
            ->states('user', 'deleted')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        $userFromAnotherCustomer = factory(User::class)
            ->states('user')
            ->create();

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson('/users')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'customer_id',
                        'vat_number',
                        'name',
                        'email',
                        'phone',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'email_verified_at',
                    ],
                ],
                'links' => [
                    'first', 'prev', 'next', 'last',
                ],
            ])
            ->assertJsonFragment([
                'id' => $otherUser->id,
            ])
            ->assertJsonMissing([
                'id' => $deletedUser->id,
            ])
            ->assertJsonMissing([
                'id' => $userFromAnotherCustomer->id,
            ]);
    }

    /** @test */
    public function it_doesnt_list_authenticated_user(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson('/users?deleted=true')
            ->assertOk()
            ->assertJsonMissing([
                'id' => $this->systemOperator->id,
            ]);
    }

    /** @test */
    public function system_operators_can_list_deleted_resources_if_requested(): void
    {
        $deletedUser = factory(User::class)
            ->states('user', 'deleted')
            ->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson('/users?deleted=true')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $deletedUser->id,
            ]);
    }

    /** @test */
    public function users_can_list_deleted_resources_related_with_their_customer_if_requested(): void
    {
        $deletedUser = factory(User::class)
            ->states('user', 'deleted')
            ->create([
                'customer_id' => $this->user->customer_id,
            ]);

        Passport::actingAs($this->user, [
            'organization:users:view',
        ]);

        $this->getJson('/users?deleted=true')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $deletedUser->id,
            ]);
    }

    /** @test */
    public function system_operators_can_filter_resources_by_customer(): void
    {
        $users = factory(User::class, 2)
            ->states('user')
            ->create();

        $customerId = $users->first()->customer_id;

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson("/users?customer={$customerId}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $users->first()->id,
            ])
            ->assertJsonMissing([
                'id' => $users->get(1)->id,
            ]);
    }

    /** @test */
    public function it_includes_customer_information_if_requested(): void
    {
        factory(User::class)
            ->states('user')
            ->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson('/users?include=customer')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'customer',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_navigates_to_next_page(): void
    {
        factory(User::class, 15)->create();

        Passport::actingAs($this->systemOperator, [
            'organization:users:view',
        ]);

        $this->getJson('/users?page=2')
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                ],
            ]);
    }
}
