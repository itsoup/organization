<?php

namespace Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Domains\Users\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Response;

class UsersUpdateController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(UserUpdateRequest $request, int $userId)
    {
        $resource = $this->users->findOrFail($userId);

        $resource->update(
            $request->only('name', 'email', 'vat_number', 'phone', 'customer_id')
        );

        return Response::create('', Response::HTTP_NO_CONTENT);
    }
}
