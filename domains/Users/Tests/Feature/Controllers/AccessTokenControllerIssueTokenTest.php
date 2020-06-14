<?php

namespace Domains\Users\Tests\Feature\Controllers;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Lcobucci\JWT\Parser;
use Tests\TestCase;

class AccessTokenControllerIssueTokenTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Client $passportClient;
    private User $user;

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
            ->create([
                'password' => 'secret',
            ]);
    }

    /** @test */
    public function it_issues_a_jwt_token_with_required_correct_user_information(): void
    {
        $accessToken = $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->passportClient->id,
            'client_secret' => $this->passportClient->secret,
            'username' => $this->user->email,
            'password' => 'secret',
            'scopes' => '*',
        ])
            ->assertSuccessful()
            ->decodeResponseJson('access_token');

        $decodedJwt = (new Parser())->parse($accessToken);

        $this->assertEquals($this->user->id, $decodedJwt->getClaim('sub'));
        $this->assertEquals($this->user->customer_id, $decodedJwt->getClaim('customer_id'));
        $this->assertEquals($this->user->vat, $decodedJwt->getClaim('vat'));
        $this->assertEquals($this->user->name, $decodedJwt->getClaim('name'));
        $this->assertEquals($this->user->email, $decodedJwt->getClaim('email'));
    }
}
