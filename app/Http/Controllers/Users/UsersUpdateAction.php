<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Response;

class UsersUpdateAction extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth:sanctum');

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
