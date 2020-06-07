<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsersIndexAction extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth:sanctum');

        $this->users = $users;
    }

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $resources = $this->users
            ->whereKeyNot($request->user()->id)
            ->when($request->user()->isUser(), static fn (Builder $users) => $users->customerId($request->user()->customer_id))
            ->when($request->input('deleted'), static fn (Builder $users) => $users->withTrashed())
            ->when($request->input('customer'), static fn (Builder $users, int $customerId) => $users->customerId($customerId))
            ->when($request->input('include'), static fn (Builder $users, string $relations) => $users->with($relations))
            ->simplePaginate();

        return UserResource::collection($resources);
    }
}
