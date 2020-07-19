<?php

namespace Domains\Users\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Requests\Me\MeUpdateRequest;
use Illuminate\Http\Response;

class MeUpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function __invoke(MeUpdateRequest $request): Response
    {
        $request->user()->update(
            $request->only('name', 'email', 'vat_number', 'phone', 'password'),
        );

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
