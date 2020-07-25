<?php

namespace Domains\Users\Tests\Feature\Controllers\Me;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Tests\TestCase;

class MeTokensDeleteControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Client $passportClient;
    private User $user;
    private Token $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportClient = Client::forceCreate([
            'name' => $this->faker->company,
            'secret' => Str::random(40),
            'redirect' => $this->faker->url,
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        $this->user = factory(User::class)
            ->state('user')
            ->create();

        $this->token = $this->user->tokens()->create([
            'id' => Str::random(100),
            'user_id' => $this->user->id,
            'client_id' => $this->passportClient->id,
            'revoked' => false,
            'name' => '',
            'scopes' => [],
        ]);
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->deleteJson("/me/tokens/{$this->token->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function it_deletes_a_resource(): void
    {
        Passport::actingAs($this->user);

        $this->deleteJson("/me/tokens/{$this->token->id}")
            ->assertNoContent();

        $this->assertDeleted(new Token(), [
            'id' => $this->token->id,
        ]);
    }

    /** @test */
    public function it_cant_delete_resources_of_other_users(): void
    {
        $anotherUser = factory(User::class)
            ->state('user')
            ->create();

        $anotherUserToken = $anotherUser->tokens()->create([
            'id' => Str::random(100),
            'user_id' => $this->user->id,
            'client_id' => $this->passportClient->id,
            'revoked' => false,
            'name' => '',
            'scopes' => [],
        ]);

        Passport::actingAs($this->user);

        $this->deleteJson("/me/tokens/{$anotherUserToken->id}")
            ->assertNotFound();
    }
}
