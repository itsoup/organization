<?php


namespace Domains\Users\Tests\Feature\Controllers\Verification;

use Domains\Users\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)
            ->states('user', 'unverified')
            ->create();
    }

    /** @test */
    public function it_fails_if_url_is_not_properly_signed(): void
    {
        $hash = Str::random();

        $this->get("/email/verify/{$this->user->id}/{$hash}")
            ->assertForbidden();
    }

    /** @test */
    public function it_verifies_unverified_users(): void
    {
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->user->getKey(),
                'hash' => sha1($this->user->getEmailForVerification()),
            ]
        );

        $this->get($verificationUrl)
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertTrue($this->user->fresh()->hasVerifiedEmail());

        Event::assertDispatched(
            fn (Verified $event) => $event->user->is($this->user)
        );
    }

    /** @test */
    public function it_handles_already_verified_users_gracefully(): void
    {
        $this->user->markEmailAsVerified();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->user->getKey(),
                'hash' => sha1($this->user->getEmailForVerification()),
            ]
        );

        $this->get($verificationUrl)
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertTrue($this->user->fresh()->hasVerifiedEmail());
    }

    /** @test */
    public function it_fails_if_hash_is_not_of_the_given_user(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->user->getKey(),
                'hash' => sha1($this->faker->safeEmail),
            ]
        );

        $this->get($verificationUrl)
            ->assertForbidden();
    }
}
