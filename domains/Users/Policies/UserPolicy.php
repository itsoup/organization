<?php

namespace Domains\Users\Policies;

use Domains\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $authenticatedUser, string $ability): ?bool
    {
        if ($authenticatedUser->isSystemOperator()) {
            return true;
        }

        return null;
    }

    public function viewAny(): bool
    {
        return false;
    }

    public function delete(User $authenticatedUser, User $resource): bool
    {
        return $authenticatedUser->customer_id === $resource->customer_id;
    }
}
