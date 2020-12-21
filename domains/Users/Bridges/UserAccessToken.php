<?php

namespace Domains\Users\Bridges;

use DateTimeImmutable;
use Domains\Users\Models\User;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Laravel\Passport\Bridge\Scope;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

class UserAccessToken extends PassportAccessToken
{
    use AccessTokenTrait;

    private User $user;

    public function __construct($userIdentifier, array $scopes, ClientEntityInterface $client)
    {
        parent::__construct($userIdentifier, $scopes, $client);

        $this->user = User::with('roles')->find($userIdentifier);
    }

    public function getScopes(): array
    {
        return $this->user
            ->roles()
            ->pluck('scopes')
            ->flatten()
            ->unique()
            ->map(static function ($scope) {
                return new Scope($scope);
            })
            ->toArray();
    }

    private function convertToJWT(): Token
    {
        $this->initJwtConfiguration();

        return $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->withClaim('customer_id', $this->user->customer_id)
            ->withClaim('vat_number', $this->user->vat_number)
            ->withClaim('name', $this->user->name)
            ->withClaim('email', $this->user->email)
            ->withClaim('account_type', $this->user->account_type)
            ->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }
}
