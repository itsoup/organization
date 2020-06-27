<?php

namespace Domains\Customers\Tests\Feature\Controllers;

use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CustomersUpdateControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $systemOperator;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemOperator = factory(User::class)
            ->state('system-operator')
            ->create();

        $this->customer = factory(Customer::class)->create();
    }

    /** @test */
    public function unauthenticated_users_cant_access_endpoint(): void
    {
        $this->patchJson("/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_update_resource(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Passport::actingAs($user);

        $this->patchJson("/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_updates_a_resource(): void
    {
        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson("/customers/{$this->customer->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'id' => $this->customer->id,
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'address' => $payload['address'],
            'country' => $payload['country'],
        ]);
    }

    /** @test */
    public function it_fails_to_update_non_existent_resources(): void
    {
        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson('/customers/2', $payload)
            ->assertNotFound();
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson("/customers/{$this->customer->id}")
            ->assertJsonValidationErrors([
                'name', 'vat_number', 'country',
            ]);
    }

    /** @test */
    public function it_fails_if_vat_number_is_already_register_for_another_resource(): void
    {
        $existingCustomer = factory(Customer::class)->create();

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $existingCustomer->vat_number,
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson("/customers/{$this->customer->id}", $payload)
            ->assertJsonValidationErrors([
                'vat_number',
            ]);
    }

    /** @test */
    public function it_updates_resource_ignoring_its_vat_number_uniqueness(): void
    {
        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->customer->vat_number,
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson("/customers/{$this->customer->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'id' => $this->customer->id,
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'address' => $payload['address'],
            'country' => $payload['country'],
        ]);
    }

    /** @test */
    public function it_updates_resource_logo_when_requested(): void
    {
        Storage::fake();

        // creates a logo to replace
        $this->customer->logo = $existingLogo = UploadedFile::fake()->image('logo.png')->store('customers');
        $this->customer->save();

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
            'logo' => UploadedFile::fake()->image('new_logo.png'),
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson("/customers/{$this->customer->id}", $payload)
            ->assertNoContent();

        Storage::assertExists('customers/' . $payload['logo']->hashname());

        $this->assertDatabaseHas('customers', [
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'country' => $payload['country'],
            'logo' => 'customers/' . $payload['logo']->hashname(),
        ]);

        Storage::assertMissing($existingLogo);
    }

    /** @test */
    public function it_keeps_current_resource_logo_if_a_new_one_is_not_sent(): void
    {
        Storage::fake();

        $this->customer->update([
            'logo' => UploadedFile::fake()->image('logo.png')->store('customers'),
        ]);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
        ];

        Passport::actingAs($this->systemOperator, [
            'organization:customers:view',
            'organization:customers:manage',
        ]);

        $this->patchJson("/customers/{$this->customer->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'country' => $payload['country'],
            'logo' => $this->customer->logo,
        ]);
    }
}
