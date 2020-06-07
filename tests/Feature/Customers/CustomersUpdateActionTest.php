<?php

namespace Tests\Feature\Customers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomersUpdateActionTest extends TestCase
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
        $this->patchJson("/organization/customers/{$this->customer->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Sanctum::actingAs($user);

        $this->patchJson("/organization/customers/{$this->customer->id}")
            ->assertForbidden();
    }

    /** @test */
    public function it_updates_a_customer(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        $this->patchJson("/organization/customers/{$this->customer->id}", $payload)
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
    public function it_fails_to_update_non_existent_customers(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        $this->patchJson('/organization/customers/2', $payload)
            ->assertNotFound();
    }

    /** @test */
    public function it_fails_if_input_is_invalid(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $this->patchJson("/organization/customers/{$this->customer->id}")
            ->assertJsonValidationErrors([
                'name', 'vat_number', 'country',
            ]);
    }

    /** @test */
    public function it_fails_if_vat_number_is_already_register_for_another_customer(): void
    {
        $existingCustomer = factory(Customer::class)->create();

        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $existingCustomer->vat_number,
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        $this->patchJson("/organization/customers/{$this->customer->id}", $payload)
            ->assertJsonValidationErrors([
                'vat_number',
            ]);
    }

    /** @test */
    public function it_updates_if_vat_number_is_of_the_self_customer(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->customer->vat_number,
            'address' => $this->faker->address,
            'country' => $this->faker->countryCode,
        ];

        $this->patchJson("/organization/customers/{$this->customer->id}", $payload)
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
    public function it_updates_logo_when_available_on_the_request(): void
    {
        Storage::fake();

        // creates a logo to replace
        $this->customer->logo = $existingLogo = UploadedFile::fake()->image('logo.png')->store('customers');
        $this->customer->save();

        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
            'logo' => UploadedFile::fake()->image('new_logo.png'),
        ];

        $this->patchJson("/organization/customers/{$this->customer->id}", $payload)
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
    public function it_keeps_current_logo_if_a_new_logo_isnt_sent(): void
    {
        $this->customer->update([
            'logo' => UploadedFile::fake()->image('logo.png')->store('customers'),
        ]);

        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
        ];

        $this->patchJson("/organization/customers/{$this->customer->id}", $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('customers', [
            'name' => $payload['name'],
            'vat_number' => $payload['vat_number'],
            'country' => $payload['country'],
            'logo' => $this->customer->logo,
        ]);
    }
}
