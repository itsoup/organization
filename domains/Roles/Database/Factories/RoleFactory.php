<?php

namespace Domains\Roles\Database\Factories;

use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Roles\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'customer_id' => static fn () => CustomerFactory::new()->create(),
            'name' => $this->faker->word,
            'scopes' => [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
                'organization:users:view',
                'organization:users:manage',
                'assets-active-directory:locations:view',
                'assets-active-directory:locations:manage',
                'assets-active-directory:assets:view',
                'assets-active-directory:assets:manage',
                'assets-active-directory:properties:view',
                'assets-active-directory:properties:manage',
            ],
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }

    public function deleted(): self
    {
        return $this->state([
            'deleted_at' => now(),
        ]);
    }
}
