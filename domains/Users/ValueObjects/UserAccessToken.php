<?php

namespace Domains\Users\ValueObjects;

use Domains\Users\Models\User;
use Laravel\Passport\Bridge\AccessToken as PassportAccessToken;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

class UserAccessToken extends PassportAccessToken
{
    use AccessTokenTrait;

    private User $user;

    public function __construct($userIdentifier, array $scopes, ClientEntityInterface $client)
    {
        parent::__construct($userIdentifier, $scopes, $client);

        $this->user = User::find($userIdentifier);
    }

    private function convertToJWT(CryptKey $privateKey): Token
    {
        return (new Builder())
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(\time())
            ->canOnlyBeUsedAfter(\time())
            ->expiresAt($this->getExpiryDateTime()->getTimestamp())
            ->relatedTo((string) $this->getUserIdentifier())
            ->withClaim('scopes', $this->getScopes())
            ->withClaim('customer_id', $this->user->customer_id)
            ->withClaim('vat', $this->user->vat)
            ->withClaim('name', $this->user->name)
            ->withClaim('email', $this->user->email)
            ->getToken(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()));
    }
}
