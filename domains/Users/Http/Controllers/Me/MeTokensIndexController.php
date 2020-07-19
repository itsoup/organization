<?php

namespace Domains\Users\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Resources\UserTokenResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeTokensIndexController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        return UserTokenResource::collection(
            $request->user()->tokens
        );
    }
}
