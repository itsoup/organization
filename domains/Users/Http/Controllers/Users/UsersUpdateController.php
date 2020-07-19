<?php

namespace Domains\Users\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Domains\Users\Http\Requests\Users\UserUpdateRequest;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class UsersUpdateController extends Controller
{
    private User $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');

        $this->users = $users;
    }

    public function __invoke(UserUpdateRequest $request, int $userId)
    {
        $resource = $this->users
            ->when($request->user()->isUser(), fn (Builder $users) => $users->customerId($request->user()->customer_id))
            ->findOrFail($userId);

        $resource->update(
            $request->only('name', 'email', 'vat_number', 'phone', 'customer_id')
        );

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
