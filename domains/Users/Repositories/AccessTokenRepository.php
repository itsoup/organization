<?php

namespace Domains\Users\Repositories;

use Domains\Users\ValueObjects\UserAccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class AccessTokenRepository extends PassportAccessTokenRepository
{
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new UserAccessToken($userIdentifier, $scopes, $clientEntity);
    }
}
