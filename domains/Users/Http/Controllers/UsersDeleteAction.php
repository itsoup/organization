<?php

namespace Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Illuminate\Http\Response;

class UsersDeleteAction extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(int $userId)
    {
        $this->users->findOrFail($userId)->delete();

        return Response::create('', Response::HTTP_NO_CONTENT);
    }
}
