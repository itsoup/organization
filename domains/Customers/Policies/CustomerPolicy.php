<?php

namespace Domains\Customers\Policies;

use Domains\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function before(User $authenticatedUser): ?bool
    {
        if (! $authenticatedUser->isSystemOperator()
            || ! $authenticatedUser->tokenCan('organization:customers:view')
        ) {
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
        return $authenticatedUser->tokenCan('organization:customers:manage');
    }

    public function update(User $authenticatedUser): bool
    {
        return $authenticatedUser->tokenCan('organization:customers:manage');
    }

    public function delete(User $authenticatedUser): bool
    {
        return $authenticatedUser->tokenCan('organization:customers:manage');
    }
}
