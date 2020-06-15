<?php

namespace Domains\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Roles\Http\Requests\RoleStoreRequest;
use Domains\Roles\Models\Role;
use Illuminate\Http\Response;

class RolesStoreController extends Controller
{
    private Role $roles;

    public function __construct(Role $roles)
    {
        $this->middleware('auth');

        $this->roles = $roles;
    }

    public function __invoke(RoleStoreRequest $request): Response
    {
        $this->roles->create([
            'customer_id' => $request->user()->customer_id,
            'name' => $request->input('name'),
            'scopes' => $request->input('scopes'),
        ]);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
