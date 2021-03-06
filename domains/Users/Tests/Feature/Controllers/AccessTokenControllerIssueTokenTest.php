<?php

namespace Domains\Users\Tests\Feature\Controllers;

use Domains\Roles\Database\Factories\RoleFactory;
use Domains\Roles\Models\Role;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Tests\TestCase;

class AccessTokenControllerIssueTokenTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Client $passportClient;
    private User $user;
    private Role $role;

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

        $this->user = UserFactory::new([
            'password' => 'secret',
        ])
            ->user()
            ->create();

        $this->role = RoleFactory::new([
            'customer_id' => $this->user->customer_id,
        ])->create();

        $this->user->roles()->sync($this->role);
    }

    /** @test */
    public function it_issues_a_jwt_token_with_required_correct_user_information(): void
    {
        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->passportClient->id,
            'client_secret' => $this->passportClient->secret,
            'username' => $this->user->email,
            'password' => 'secret',
        ])
            ->assertOk()
            ->decodeResponseJson();

        /** @var Plain $decodedJwt */
        $decodedJwt = Configuration::forUnsecuredSigner()
            ->parser()
            ->parse($response['access_token']);

        self::assertEquals($this->user->id, $decodedJwt->claims()->get('sub'));
        self::assertEquals($this->user->customer_id, $decodedJwt->claims()->get('customer_id'));
        self::assertEquals($this->user->vat_number, $decodedJwt->claims()->get('vat_number'));
        self::assertEquals($this->user->name, $decodedJwt->claims()->get('name'));
        self::assertEquals($this->user->email, $decodedJwt->claims()->get('email'));
        self::assertEquals($this->user->account_type, $decodedJwt->claims()->get('account_type'));
        self::assertEquals($this->role->scopes, $decodedJwt->claims()->get('scopes'));
    }

    /** @test */
    public function it_doesnt_issues_jwt_to_unverified_users(): void
    {
        $unverifiedUser = UserFactory::new([
            'password' => 'secret',
        ])
            ->user()
            ->unverified()
            ->create();

        $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $this->passportClient->id,
            'client_secret' => $this->passportClient->secret,
            'username' => $unverifiedUser->email,
            'password' => 'secret',
        ])
            ->assertStatus(Response::HTTP_BAD_REQUEST);
    }
}
