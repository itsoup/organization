<?php

namespace Domains\Users\Tests\Unit\Models;

use Domains\Customers\Models\Customer;
use Domains\Roles\Models\Role;
use Domains\Users\Casts\PasswordCast;
use Domains\Users\Database\Factories\UserFactory;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

        $this->model = UserFactory::new([
            'id' => 1,
        ])->make();
    }

    /** @test */
    public function it_has_required_properties(): void
    {
        self::assertNull($this->model->customer_id);
        self::assertIsString($this->model->name);
        self::assertIsString($this->model->vat_number);
        self::assertIsString($this->model->email);
        self::assertIsString($this->model->password);
        self::assertIsString($this->model->phone);

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

    /** @test */
    public function it_has_tokens_relation(): void
    {
        self::assertInstanceOf(HasMany::class, $this->model->tokens());
        self::assertInstanceOf(Token::class, $this->model->tokens()->getModel());
    }

    /** @test */
    public function it_has_customer_relation(): void
    {
        self::assertInstanceOf(Customer::class, $this->model->customer()->getModel());
    }

    /** @test */
    public function it_checks_if_user_is_a_system_operator(): void
    {
        $this->model->customer_id = null;

        self::assertTrue($this->model->isSystemOperator());
        self::assertEquals('system-operator', $this->model->account_type);
    }

    /** @test */
    public function it_checks_if_user_is_a_user(): void
    {
        $this->model->customer_id = 1;

        self::assertTrue($this->model->isUser());
        self::assertEquals('user', $this->model->account_type);
    }

    /** @test */
    public function it_has_custom_password_cast(): void
    {
        self::assertTrue($this->model->hasCast('password', strtolower(PasswordCast::class)));
    }

    /** @test */
    public function it_has_roles_relation(): void
    {
        self::assertInstanceOf(Role::class, $this->model->roles()->getModel());
    }

    /** @test */
    public function it_gets_correct_identifier(): void
    {
        self::assertEquals($this->model->id, $this->model->getIdentifier());
    }
}
