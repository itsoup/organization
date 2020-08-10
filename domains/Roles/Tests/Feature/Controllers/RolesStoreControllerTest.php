<?php

namespace Domains\Roles\Tests\Feature\Controllers;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class RolesStoreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private User $user;

    public function rolesScopesProvider(): array
    {
        return [
            'Organization Scopes' => [
                [
                    'organization:roles:view',
                    'organization:roles:manage',
                    'organization:users:view',
                    'organization:users:manage',
                ],
            ],
            'Assets Active Directory Scopes' => [
                [
                    'assets-active-directory:locations:view',
                    'assets-active-directory:locations:manage',
                    'assets-active-directory:assets:view',
                    'assets-active-directory:assets:manage',
                    'assets-active-directory:properties:view',
                    'assets-active-directory:properties:manage',
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->user = factory(User::class)
            ->state('user')
            ->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->postJson('/roles')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_create_resources(): void
    {
        Passport::actingAs($this->user);

        $this->postJson('/roles')
            ->assertForbidden();
    }

    /** @test */
    public function it_fails_if_missing_required_input(): void
    {
        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles')
            ->assertJsonValidationErrors([
                'name',
                'scopes',
            ]);
    }

    /** @test */
    public function it_fails_if_scopes_have_invalid_value(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'invalid:scope:value',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertJsonValidationErrors([
                'scopes.0',
            ]);
    }

    /**
     * @test
     * @dataProvider rolesScopesProvider
     *
     * @param array $scopes
     */
    public function users_can_store_new_resources_automatically_associated_with_their_customer_id(array $scopes): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => $scopes,
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'customer_id' => $this->user->customer_id,
            'name' => $payload['name'],
            'scopes' => json_encode($payload['scopes']),
        ]);
    }

    /**
     * @test
     * @dataProvider rolesScopesProvider
     *
     * @param array $scopes
     */
    public function system_operators_can_store_new_resources_automatically_associated_with_their_customer_id(array $scopes): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => $scopes,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('roles', [
            'customer_id' => $this->systemOperator->customer_id,
            'name' => $payload['name'],
            'scopes' => json_encode($payload['scopes']),
        ]);
    }

    /** @test */
    public function users_cant_store_resources_with_scopes_for_customers_module(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
                'organization:users:view',
                'organization:users:manage',
            ],
        ];

        Passport::actingAs($this->user, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertJsonValidationErrors([
                'scopes.0',
                'scopes.1',
            ]);
    }

    /** @test */
    public function system_operators_can_store_resources_with_scopes_for_customers_module(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'scopes' => [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
                'organization:users:view',
                'organization:users:manage',
            ],
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:roles:view',
            'organization:roles:manage',
        ]);

        $this->postJson('/roles', $payload)
            ->assertNoContent();
    }
}
