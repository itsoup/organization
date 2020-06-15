<?php

namespace Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Resources\UserResource;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UsersShowController extends Controller
{
    /** @var User */
    private User $users;

    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function __invoke(Request $request, int $userId): UserResource
    {
        $resource = $this->users
            ->when($request->input('include'), static fn (Builder $users, string $relations) => $users->with($relations))
            ->findOrFail($userId);

        $this->authorize('view', $resource);

        return UserResource::make($resource);
    }
}
