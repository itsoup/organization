<?php


use Domains\Roles\Models\Role;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Role::class, static fn(\Faker\Generator $faker) => [
    'customer_id' => null,
    'name' => $faker->word,
    'scopes' => [
        'organization:customers:view',
        'organization:customers:manage',
        'organization:roles:view',
        'organization:roles:manage',
        'organization:users:view',
        'organization:users:manage',
    ],
    'created_at' => now(),
    'updated_at' => now(),
    'deleted_at' => null,
]);

$factory->state(Role::class, 'deleted', [
    'deleted_at' => now(),
]);
