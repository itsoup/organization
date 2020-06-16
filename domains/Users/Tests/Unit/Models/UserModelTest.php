<?php

namespace Domains\Users\Tests\Unit\Models;

use Domains\Roles\Models\Role;
use Domains\Users\Casts\PasswordCast;
use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Laravel\Passport\Token;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = factory(User::class)->make([
            'id' => 1,
        ]);
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
        $this->assertInstanceOf(HasMany::class, $this->model->tokens());
        $this->assertInstanceOf(Token::class, $this->model->tokens()->getModel());
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
        $this->assertEquals('system-operator', $this->model->account_type);
    }

    /** @test */
    public function it_checks_if_user_is_a_user(): void
    {
        $this->model->customer_id = 1;

        $this->assertTrue($this->model->isUser());
        $this->assertEquals('user', $this->model->account_type);
    }

    /** @test */
    public function it_has_custom_password_cast(): void
    {
        $this->assertTrue($this->model->hasCast('password', strtolower(PasswordCast::class)));
    }

    /** @test */
    public function it_has_roles_relation(): void
    {
        $this->assertInstanceOf(Role::class, $this->model->roles()->getModel());
    }

    /** @test */
    public function it_gets_correct_identifier(): void
    {
        $this->assertEquals($this->model->id, $this->model->getIdentifier());
    }
}
