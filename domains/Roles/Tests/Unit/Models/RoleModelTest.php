<?php

namespace Domains\Roles\Tests\Unit\Models;

use Domains\Customers\Models\Customer;
use Domains\Roles\Models\Role;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RoleModelTest extends TestCase
{
    private Role $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = factory(Role::class)->make([
            'customer_id' => null,
        ]);
    }

    /** @test */
    public function it_has_required_properties(): void
    {
        self::assertNull($this->model->customer_id);
        self::assertIsString($this->model->name);

        self::assertIsArray($this->model->scopes);
        self::assertEquals(
            [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
                'organization:users:view',
                'organization:users:manage',
                'assets-active-directory:locations:view',
                'assets-active-directory:locations:manage',
                'assets-active-directory:assets:view',
                'assets-active-directory:assets:manage',
                'assets-active-directory:properties:view',
                'assets-active-directory:properties:manage',

            ],
            $this->model->scopes
        );

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
    public function it_cats_scopes_as_array(): void
    {
        self::assertTrue($this->model->hasCast('scopes', 'array'));
    }

    /** @test */
    public function it_has_users_relation(): void
    {
        self::assertInstanceOf(User::class, $this->model->users()->getModel());
    }

    /** @test */
    public function it_has_customer_relation(): void
    {
        self::assertInstanceOf(Customer::class, $this->model->customer()->getModel());
    }
}
