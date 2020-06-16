<?php

namespace Domains\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Roles\Http\Requests\RoleUpdateRequest;
use Domains\Roles\Models\Role;
use Illuminate\Http\Response;

class RolesUpdateController extends Controller
{
    private Role $roles;

    public function __construct(Role $roles)
    {
        $this->middleware('auth');

        $this->roles = $roles;
    }

    public function __invoke(RoleUpdateRequest $request, int $roleId): Response
    {
        $resource = $this->roles->findOrFail($roleId);

        $resource->update([
            'name' => $request->input('name'),
            'scopes' => $request->input('scopes'),
        ]);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
