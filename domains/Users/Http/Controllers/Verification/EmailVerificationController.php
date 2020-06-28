<?php

namespace Domains\Users\Http\Controllers\Verification;

use App\Http\Controllers\Controller;
use Domains\Users\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware(['signed']);

        $this->users = $users;
    }

    public function __invoke(Request $request, int $userId, string $hash): RedirectResponse
    {
        $user = $this->users->findOrFail($userId);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->hasVerifiedEmail()) {
            return back();
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return back();
    }
}
