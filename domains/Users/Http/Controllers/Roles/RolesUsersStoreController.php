<?php

namespace Domains\Users\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Requests\Roles\RoleUserStoreRequest;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class RolesUsersStoreController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(RoleUserStoreRequest $request, int $userId)
    {
        $resource = $this->users
            ->withTrashed()
            ->when(
                $request->user()->customer_id,
                static fn (Builder $users, int $customerId) => $users->customerId($customerId),
                static fn (Builder $users) => $users->systemOperators()
            )
            ->findOrFail($userId);

        $resource->roles()->syncWithoutDetaching($request->input('roles'));

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
