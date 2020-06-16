<?php

namespace Domains\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Roles\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RolesDeleteController extends Controller
{
    /** @var Role */
    private Role $roles;

    public function __construct(Role $roles)
    {
        $this->middleware('auth');

        $this->roles = $roles;
    }

    public function __invoke(Request $request, int $roleId): Response
    {
        $resource = $this->roles
            ->customerId($request->user()->customer_id)
            ->findOrFail($roleId);

        $resource->delete();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
