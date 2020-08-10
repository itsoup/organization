<?php


use Domains\Customers\Models\Customer;
use Domains\Roles\Models\Role;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Role::class, static fn(\Faker\Generator $faker) => [
    'customer_id' => static fn() => factory(Customer::class)->create(),
    'name' => $faker->word,
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
]);

$factory->state(Role::class, 'deleted', [
    'deleted_at' => now(),
]);
