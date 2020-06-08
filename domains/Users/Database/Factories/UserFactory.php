<?php

use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Support\Str;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(User::class, static fn (\Faker\Generator $faker) => [
    'customer_id' => null,
    'name' => $faker->name,
    'vat_number' => $faker->countryCode . $faker->randomNumber(9),
    'email' => $faker->unique()->safeEmail,
    'email_verified_at' => now(),
    'password' => 'password',
    'remember_token' => Str::random(10),
    'phone' => $faker->phoneNumber,
    'created_at' => now(),
    'updated_at' => now(),
    'deleted_at' => null,
]);

$factory->state(User::class, 'system-operator', static fn () => [
    'customer_id' => null,
]);

$factory->state(User::class, 'user', static fn () => [
    'customer_id' => static fn () => factory(Customer::class)->create(),
]);

$factory->state(User::class, 'deleted', static fn () => [
    'deleted_at' => now(),
]);
