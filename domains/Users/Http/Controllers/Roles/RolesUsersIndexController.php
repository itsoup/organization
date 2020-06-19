<?php

namespace Domains\Users\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Domains\Roles\Http\Resources\RoleResource;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RolesUsersIndexController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(Request $request, int $userId)
    {
        $resource = $this->users
            ->when(
                $request->user()->customer_id,
                static fn (Builder $users, int $customerId) => $users->customerId($customerId),
                static fn (Builder $users) => $users->systemOperators()
            )
            ->findOrFail($userId);

        $this->authorize('view', $resource);

        return RoleResource::collection($resource->roles);
    }
}
