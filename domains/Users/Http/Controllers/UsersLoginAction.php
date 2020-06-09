<?php

namespace Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Domains\Users\Http\Requests\UserLoginRequest;
use Domains\Users\Http\Resources\AccessTokenResource;
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

        $user->revokePreviousTokens($request->client_name);

        return AccessTokenResource::make($user->createToken($request->client_name));
    }
}
