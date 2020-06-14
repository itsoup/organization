<?php

namespace Domains\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Domains\Users\Http\Requests\UserStoreRequest;
use Illuminate\Http\Response;

class UsersStoreAction extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(UserStoreRequest $request): Response
    {
        $this->users->create([
            'customer_id' => $request->input('customer_id', $request->user()->customer_id),
            'name' => $request->input('name'),
            'vat_number' => $request->input('vat_number'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        return Response::create('', Response::HTTP_CREATED);
    }
}
