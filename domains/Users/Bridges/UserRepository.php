<?php

namespace Domains\Users\Bridges;

use Domains\Users\Models\User;
use Laravel\Passport\Bridge\UserRepository as PassportUserRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use RuntimeException;

class UserRepository extends PassportUserRepository
{
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity): ?User
    {
        $provider = $clientEntity->provider ?: config('auth.guards.api.provider');

        if (is_null($model = config('auth.providers.' . $provider . '.model'))) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (method_exists($model, 'findAndValidateForPassport')) {
            if (! $user = (new $model)->findAndValidateForPassport($username, $password)) {
                return null;
            }

            return $user;
        }

        if (method_exists($model, 'findForPassport')) {
            $user = (new $model)->findForPassport($username);
        } else {
            $user = (new $model)->where('email', $username)->first();
        }

        if (! $user) {
            return null;
        }

        if (method_exists($user, 'validateForPassportPasswordGrant')) {
            if (! $user->validateForPassportPasswordGrant($password)) {
                return null;
            }
        } elseif (! $this->hasher->check($password, $user->getAuthPassword())) {
            return null;
        }

        return $user;
    }
}
