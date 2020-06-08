<?php

namespace Domains\Users\Tests\Unit;

use Domains\Users\Casts\PasswordCast;
use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = factory(User::class)->make();
    }

    /** @test */
    public function it_has_required_properties(): void
    {
        $this->assertNull($this->model->customer_id);
        $this->assertIsString($this->model->name);
        $this->assertIsString($this->model->vat_number);
        $this->assertIsString($this->model->email);
        $this->assertIsString($this->model->password);
        $this->assertIsString($this->model->phone);

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

    /** @test */
    public function it_has_tokens_relation(): void
    {
        $this->assertInstanceOf(MorphMany::class, $this->model->tokens());
        $this->assertInstanceOf(PersonalAccessToken::class, $this->model->tokens()->getModel());
    }

    /** @test */
    public function it_has_customer_relation(): void
    {
        $this->assertInstanceOf(Customer::class, $this->model->customer()->getModel());
    }

    /** @test */
    public function it_checks_if_user_is_a_system_operator(): void
    {
        $this->model->customer_id = null;

        $this->assertTrue($this->model->isSystemOperator());
    }

    /** @test */
    public function it_checks_if_user_is_a_user(): void
    {
        $this->model->customer_id = 1;

        $this->assertTrue($this->model->isUser());
    }

    /** @test */
    public function it_has_custom_password_cast(): void
    {
        $this->assertTrue($this->model->hasCast('password', strtolower(PasswordCast::class)));
    }
}
