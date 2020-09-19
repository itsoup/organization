<?php

namespace Domains\Customers\Database\Factories;

use Domains\Customers\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
            'vat_number' => fn (array $customer) => $customer['country'] . $this->faker->randomNumber(9),
            'logo' => null,
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
