<?php

namespace Domains\Users\Database\Factories;

use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'customer_id' => null,
            'name' => $this->faker->name,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
            'phone' => $this->faker->e164PhoneNumber,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }

    public function systemOperator(): self
    {
        return $this->state([
            'customer_id' => null,
        ]);
    }

    public function user(): self
    {
        return $this->state([
            'customer_id' => static fn () => CustomerFactory::new()->create(),
        ]);
    }

    public function deleted(): self
    {
        return $this->state([
            'deleted_at' => now(),
        ]);
    }

    public function unverified(): self
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }
}
