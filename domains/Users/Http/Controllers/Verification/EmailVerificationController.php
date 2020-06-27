<?php

namespace Domains\Users\Http\Controllers\Verification;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('signed');

        $this->users = $users;
    }

    public function __invoke(Request $request, int $userId, string $hash): Response
    {
        $user = $this->users->findOrFail($userId);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            return new Response('', 204);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return new Response('', 204);
    }
}
