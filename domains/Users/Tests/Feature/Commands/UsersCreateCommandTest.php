<?php

namespace Domains\Users\Tests\Feature\Commands;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UsersCreateCommandTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_is_loaded_by_artisan(): void
    {
        $this->artisan('users:create --help')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_creates_a_user_as_system_operator_without_any_argument_provided(): void
    {
        $userName = $this->faker->name;
        $userEmail = $this->faker->safeEmail;
        $userPassword = $this->faker->password;

        $this->artisan('users:create')
            ->expectsQuestion('Provide the user name', $userName)
            ->expectsQuestion('Provide the user email', $userEmail)
            ->expectsQuestion('Provide the user password', $userPassword)
            ->assertExitCode(0)
            ->expectsOutput(
                sprintf('User %s <%s> was created', $userName, $userEmail)
            );

        $this->assertDatabaseHas('users', [
            'customer_id' => null,
            'name' => $userName,
            'email' => $userEmail,
        ]);

        /** @var User $newUser */
        $newUser = User::email($userEmail)->first();

        $this->assertTrue(
            Hash::check($userPassword, $newUser->password)
        );
    }

    /** @test */
    public function it_creates_a_user_as_system_operator_with_provided_arguments(): void
    {
        $userName = $this->faker->name;
        $userEmail = $this->faker->safeEmail;
        $userPassword = $this->faker->password;

        $this->artisan(
            'users:create',
            [
                'name' => $userName,
                'email' => $userEmail,
            ]
        )
            ->expectsQuestion('Provide the user password', $userPassword)
            ->assertExitCode(0)
            ->expectsOutput(
                sprintf('User %s <%s> was created', $userName, $userEmail)
            );

        $this->assertDatabaseHas('users', [
            'customer_id' => null,
            'name' => $userName,
            'email' => $userEmail,
        ]);
    }

    /** @test */
    public function it_fails_command_execution_if_arguments_fail_validation(): void
    {
        $this->expectException(ValidationException::class);

        $this->artisan('users:create')
            ->expectsQuestion('Provide the user name', '')
            ->expectsQuestion('Provide the user email', '')
            ->expectsQuestion('Provide the user password', '');
    }
}
