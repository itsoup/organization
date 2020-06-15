<?php

namespace Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Resources\UserResource;
use Domains\Users\Models\User;

class UsersShowController extends Controller
{
    /** @var User */
    private User $users;

    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function __invoke(int $userId): UserResource
    {
        $resource = $this->users->findOrFail($userId);

        $this->authorize('view', $resource);

        return UserResource::make($resource);
    }
}
