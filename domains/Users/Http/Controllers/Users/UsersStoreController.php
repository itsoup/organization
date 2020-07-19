<?php

namespace Domains\Users\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Requests\Users\UserStoreRequest;
use Domains\Users\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Response;

class UsersStoreController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(UserStoreRequest $request): Response
    {
        $newUser = $this->users->create([
            'customer_id' => $request->input('customer_id', $request->user()->customer_id),
            'name' => $request->input('name'),
            'vat_number' => $request->input('vat_number'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        event(new Registered($newUser));

        return new Response('', Response::HTTP_CREATED);
    }
}
