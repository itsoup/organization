<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsersCreateCommand extends Command
{
    protected $signature = 'users:create
                            {name? : The user\'s name}
                            {email? : The user\'s email}
                            {--t|type=system-operator : The type of the user to create}';

    protected $description = 'Creates a new user on the system';

    private User $users;

    public function __construct(User $users)
    {
        parent::__construct();

        $this->users = $users;
    }

    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Provide the user name');
        $email = $this->argument('email') ?? $this->ask('Provide the user email');
        $password = $this->secret('Provide the user password');

        $this->validatesArguments($name, $email, $password);

        $this->users->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info(
            sprintf('User %s <%s> was created', $name, $email)
        );
    }

    private function validatesArguments($name, $email, $password): void
    {
        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
            [
                'name' => ['required', 'filled'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'filled'],
            ]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
