<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CustomersStoreControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->postJson('/customers')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($user);

        $this->postJson('/customers')
            ->assertForbidden();
    }

    /** @test */
    public function authorized_system_operators_can_store_new_customers(): void
    {
        Passport::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
            'address' => $this->faker->address,
        ];

        $this->postJson('/customers', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('customers', [
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'country' => $payload['country'],
            'address' => $payload['address'],
        ]);
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Passport::actingAs($this->systemOperator);

        $this->postJson('/customers')
            ->assertJsonValidationErrors([
                'name', 'vat_number', 'country',
            ]);
    }

    /** @test */
    public function it_saves_logo_when_available_on_the_request(): void
    {
        Storage::fake();

        Passport::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];

        $this->postJson('/customers', $payload)
            ->assertCreated();

        Storage::assertExists('customers/' . $payload['logo']->hashname());

        $this->assertDatabaseHas('customers', [
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'country' => $payload['country'],
            'logo' => 'customers/' . $payload['logo']->hashname(),
        ]);
    }
}