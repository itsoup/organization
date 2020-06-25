<?php

namespace Domains\Users\Bridges;

use Laravel\Passport\Bridge\AccessTokenRepository as PassportAccessTokenRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class AccessTokenRepository extends PassportAccessTokenRepository
{
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): UserAccessToken
    {
        return new UserAccessToken($userIdentifier, $scopes, $clientEntity);
    }
}
