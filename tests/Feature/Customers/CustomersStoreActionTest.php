<?php

namespace Tests\Feature\Customers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomersStoreActionTest extends TestCase
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
        $this->postJson('/organization/customers')
            ->assertUnauthorized();
    }

    /** @test */
    public function unauthorized_users_cant_access_endpoint(): void
    {
        $user = factory(User::class)
            ->state('user')
            ->create();

        Sanctum::actingAs($user);

        $this->postJson('/organization/customers')
            ->assertForbidden();
    }

    /** @test */
    public function authorized_system_operators_can_store_new_customers(): void
    {
        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
            'address' => $this->faker->address,
        ];

        $this->postJson('/organization/customers', $payload)
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
        Sanctum::actingAs($this->systemOperator);

        $this->postJson('/organization/customers')
            ->assertJsonValidationErrors([
                'name', 'vat_number', 'country',
            ]);
    }

    /** @test */
    public function it_saves_logo_when_available_on_the_request(): void
    {
        Storage::fake();

        Sanctum::actingAs($this->systemOperator);

        $payload = [
            'name' => $this->faker->company,
            'vat_number' => $this->faker->countryCode . $this->faker->randomNumber(9),
            'country' => $this->faker->countryCode,
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];

        $this->postJson('/organization/customers', $payload)
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
