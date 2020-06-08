<?php

namespace Domains\Customers\Tests\Unit;

use Carbon\Carbon;
use Domains\Customers\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Tests\TestCase;

class CustomerModelTest extends TestCase
{
    private Customer $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = factory(Customer::class)->make();
    }

    /** @test */
    public function it_has_required_properties(): void
    {
        $this->assertIsString($this->model->name);
        $this->assertIsString($this->model->address);
        $this->assertIsString($this->model->country);
        $this->assertIsString($this->model->vat_number);

        $this->assertEquals(2, strlen($this->model->country));

        $this->assertInstanceOf(Carbon::class, $this->model->created_at);
        $this->assertInstanceOf(Carbon::class, $this->model->updated_at);
        $this->assertNull($this->model->deleted_at);
    }

    /** @test */
    public function it_uses_timestamps(): void
    {
        $this->assertTrue($this->model->usesTimestamps());

        $this->assertEquals('created_at', $this->model->getCreatedAtColumn());
        $this->assertEquals('updated_at', $this->model->getUpdatedAtColumn());
    }

    /** @test */
    public function it_uses_soft_deletes(): void
    {
        $this->assertArrayHasKey(SoftDeletingScope::class, $this->model->getGlobalScopes());
    }
}
