<?php

namespace Domains\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Roles\Http\Resources\RoleResource;
use Domains\Roles\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RolesShowController extends Controller
{
    private Role $roles;

    public function __construct(Role $roles)
    {
        $this->middleware('auth');

        $this->roles = $roles;
    }

    public function __invoke(Request $request, int $roleId)
    {
        $resource = $this->roles
            ->customerId($request->user()->customer_id)
            ->when($request->input('include'), static fn (Builder $roles, string $relations) => $roles->with($relations))
            ->findOrFail($roleId);

        $this->authorize('view', $resource);

        return RoleResource::make($resource);
    }
}
