<?php

namespace Domains\Users\Tests\Feature\Controllers\Me;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MeUpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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
        $this->patchJson('/me')
            ->assertUnauthorized();
    }

    /** @test */
    public function it_fails_to_update_if_required_input_is_empty(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'name' => '',
            'email' => '',
            'vat_number' => '',
        ])
            ->assertJsonValidationErrors([
                'name', 'email', 'vat_number',
            ]);
    }

    /** @test */
    public function it_fails_update_if_email_is_invalid(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'email' => 'invalid_email',
        ])
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    /** @test */
    public function it_fails_update_if_email_is_registered_to_other_user(): void
    {
        $anotherUser = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'email' => $anotherUser->email,
        ])
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    /** @test */
    public function it_fails_update_if_vat_number_is_registered_to_other_user(): void
    {
        $anotherUser = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'vat_number' => $anotherUser->vat_number,
        ])
            ->assertJsonValidationErrors([
                'vat_number',
            ]);
    }

    /** @test */
    public function it_updates_authenticated_user_information(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'phone' => $this->faker->e164PhoneNumber,
        ];

        Passport::actingAs($this->user);

        $this->patchJson('/me', $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'vat_number' => $payload['vat_number'],
            'phone' => $payload['phone'],
        ]);
    }

    /** @test */
    public function it_fails_to_update_password_if_input_is_empty(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'password' => '',
        ])
            ->assertJsonValidationErrors([
                'password',
            ]);
    }

    /** @test */
    public function it_fails_to_update_password_if_confirmation_password_is_incorrect(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'password' => 'filled-password',
            'password_confirmation' => '',
        ])
            ->assertJsonValidationErrors([
                'password',
            ]);
    }

    /** @test */
    public function it_updates_authenticated_user_password(): void
    {
        Passport::actingAs($this->user);

        $this->patchJson('/me', [
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])
            ->assertNoContent();

        self::assertTrue(
            Hash::check('another-password', $this->user->fresh()->password)
        );
    }
}
