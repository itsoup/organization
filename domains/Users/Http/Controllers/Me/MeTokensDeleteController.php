<?php

namespace Domains\Users\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MeTokensDeleteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function __invoke(Request $request, string $id)
    {
        $request->user()
            ->tokens()
            ->findOrFail($id)
            ->delete();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
