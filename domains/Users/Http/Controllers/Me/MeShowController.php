<?php

namespace Domains\Users\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Resources\UserResource;
use Illuminate\Http\Request;

class MeShowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request): UserResource
    {
        if ($request->input('include')) {
            $request->user()->load($request->input('include'));
        }

        return UserResource::make($request->user());
    }
}
