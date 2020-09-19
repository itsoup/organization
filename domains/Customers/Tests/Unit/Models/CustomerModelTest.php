<?php

namespace Domains\Customers\Tests\Unit\Models;

use Carbon\Carbon;
use Domains\Customers\Database\Factories\CustomerFactory;
use Domains\Customers\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    private Customer $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = CustomerFactory::new()->make();
    }

    /** @test */
    public function it_has_required_properties(): void
    {
        self::assertIsString($this->model->name);
        self::assertIsString($this->model->address);
        self::assertIsString($this->model->country);
        self::assertIsString($this->model->vat_number);

        self::assertEquals(2, strlen($this->model->country));

        self::assertInstanceOf(Carbon::class, $this->model->created_at);
        self::assertInstanceOf(Carbon::class, $this->model->updated_at);
        self::assertNull($this->model->deleted_at);
    }

    /** @test */
    public function it_uses_timestamps(): void
    {
        self::assertTrue($this->model->usesTimestamps());

        self::assertEquals('created_at', $this->model->getCreatedAtColumn());
        self::assertEquals('updated_at', $this->model->getUpdatedAtColumn());
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        self::assertArrayHasKey(SoftDeletingScope::class, $this->model->getGlobalScopes());
    }
}
