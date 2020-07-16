<?php

namespace Domains\Users\Tests\Feature\Controllers\Me;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MeShowControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)
            ->state('user')
            ->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->getJson('/me')
            ->assertUnauthorized();
    }

    /** @test */
    public function it_shows_authenticated_user_information(): void
    {
        Passport::actingAs($this->user);

        $this->getJson('/me')
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
    public function it_includes_authenticated_user_customer_information_if_available(): void
    {
        Passport::actingAs($this->user);

        $this->getJson('/me?include=customer')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'customer' => [
                        'id' => $this->user->customer_id,
                    ],
                ],
            ]);
    }
}
