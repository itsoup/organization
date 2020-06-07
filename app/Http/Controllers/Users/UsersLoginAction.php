<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\UserLoginRequest;
use App\Http\Resources\AccessTokenResource;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Validation\ValidationException;

class UsersLoginAction extends Controller
{
    private User $users;
    private Hasher $hasher;

    public function __construct(User $users, Hasher $hasher)
    {
        $this->users = $users;
        $this->hasher = $hasher;
    }

    public function __invoke(UserLoginRequest $request): AccessTokenResource
    {
        /** @var User $user */
        $user = $this->users->email($request->input('email'))->first();

        if (! $user || ! $this->hasher->check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return AccessTokenResource::make($user->createToken($request->client_name));
    }
}
