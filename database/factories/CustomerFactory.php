<?php

use App\Models\Customer;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Customer::class, static fn (\Faker\Generator $faker) => [
    'name' => $faker->company,
    'address' => $faker->address,
    'country' => $faker->countryCode,
    'vat_number' => static fn (array $customer) => $customer['country'] . $faker->randomNumber(9),
    'logo' => null,
    'created_at' => now(),
    'updated_at' => now(),
    'deleted_at' => null,
]);

$factory->state(Customer::class, 'deleted', static fn () => [
    'deleted_at' => now(),
]);
