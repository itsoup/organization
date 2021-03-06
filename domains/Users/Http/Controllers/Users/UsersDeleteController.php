<?php

namespace Domains\Users\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Illuminate\Http\Response;

class UsersDeleteController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(int $userId)
    {
        $resource = $this->users->findOrFail($userId);

        $this->authorize('delete', $resource);

        $resource->delete();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
