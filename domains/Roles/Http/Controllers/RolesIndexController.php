<?php

namespace Domains\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Roles\Http\Resources\RoleResource;
use Domains\Roles\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RolesIndexController extends Controller
{
    private Role $roles;

    public function __construct(Role $roles)
    {
        $this->middleware('auth');

        $this->roles = $roles;
    }

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', $this->roles);

        $resources = $this->roles
            ->customerId($request->user()->customer_id)
            ->when($request->input('deleted'), static fn (Builder $roles) => $roles->withTrashed())
            ->when($request->input('include'), static fn (Builder $roles, string $relations) => $roles->with($relations))
            ->simplePaginate();

        return RoleResource::collection($resources);
    }

}
