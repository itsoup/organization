<?php

namespace Domains\Users\Policies;

use Domains\Users\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $authenticatedUser, string $ability): ?bool
    {
        if (! $authenticatedUser->tokenCan('organization:users:view')) {
            return false;
        }

        if ($ability !== 'update' && $authenticatedUser->isSystemOperator()) {
            return true;
        }

        return null;
    }

    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $authenticatedUser, User $resource): bool
    {
        return $authenticatedUser->customer_id === $resource->customer_id;
    }

    public function create(User $authenticatedUser, bool $requestMissingCustomerId): bool
    {
        return $authenticatedUser->tokenCan('organization:users:manage')
            && (
                $authenticatedUser->isSystemOperator()
                || ($authenticatedUser->isUser() && $requestMissingCustomerId)
            );
    }

    public function update(User $authenticatedUser, int $resourceId, bool $requestMissingCustomerId): bool
    {
        return $authenticatedUser->tokenCan('organization:users:manage')
            && $authenticatedUser->id !== $resourceId
            && (
                $authenticatedUser->isSystemOperator()
                || (
                    $authenticatedUser->isUser() && $requestMissingCustomerId
                )
            );
    }

    public function delete(User $authenticatedUser, User $resource): bool
    {
        return $authenticatedUser->tokenCan('organization:users:manage')
            && $authenticatedUser->isNot($resource)
            && $authenticatedUser->customer_id === $resource->customer_id;
    }
}
