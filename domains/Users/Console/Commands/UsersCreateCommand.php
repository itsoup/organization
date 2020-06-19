<?php

namespace Domains\Users\Console\Commands;

use Domains\Roles\Models\Role;
use Domains\Users\Models\User;
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
    private Role $roles;

    public function __construct(User $users, Role $roles)
    {
        parent::__construct();

        $this->users = $users;
        $this->roles = $roles;
    }

    public function handle(): void
    {
        $name = $this->argument('name') ?? $this->ask('Provide the user name');
        $email = $this->argument('email') ?? $this->ask('Provide the user email');
        $password = $this->secret('Provide the user password');

        $this->validatesArguments($name, $email, $password);

        $user = $this->users->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        if ($this->roles->count() === 0) {
            $this->createDefaultRoleFor($user);
        }

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

    private function createDefaultRoleFor(User $user): void
    {
        $role = $this->roles->create([
            'customer_id' => $user->customer_id,
            'name' => 'Admin role',
            'scopes' => Role::getValidScopesFor($user->account_type),
        ]);

        $user->roles()->sync($role);
    }
}
