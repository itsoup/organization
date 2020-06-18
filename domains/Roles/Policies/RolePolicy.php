<?php

namespace Domains\Roles\Policies;

use Domains\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function before(User $authenticatedUser): ?bool
    {
        if (! $authenticatedUser->tokenCan('organization:roles:view')) {
            return false;
        }

        return null;
    }

    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(User $authenticatedUser): bool
    {
        return $authenticatedUser->tokenCan('organization:roles:manage');
    }

    public function update(User $authenticatedUser): bool
    {
        return $authenticatedUser->tokenCan('organization:roles:manage');
    }

    public function delete(User $authenticatedUser): bool
    {
        return $authenticatedUser->tokenCan('organization:roles:manage');
    }
}
