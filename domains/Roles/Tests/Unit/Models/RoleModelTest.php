<?php

namespace Domains\Roles\Tests\Unit\Models;

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

        $this->model = factory(Role::class)->make();
    }

    /** @test */
    public function it_has_required_properties(): void
    {
        $this->assertNull($this->model->customer_id);
        $this->assertIsString($this->model->name);

        $this->assertIsArray($this->model->scopes);
        $this->assertEquals(
            [
                'organization:customers:view',
                'organization:customers:manage',
                'organization:roles:view',
                'organization:roles:manage',
                'organization:users:view',
                'organization:users:manage',
            ],
            $this->model->scopes
        );

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
    public function it_cats_scopes_as_array(): void
    {
        $this->assertTrue($this->model->hasCast('scopes', 'array'));
    }

    /** @test */
    public function it_belongs_to_many_users(): void
    {
        $this->assertInstanceOf(User::class, $this->model->users()->getModel());
    }
}
