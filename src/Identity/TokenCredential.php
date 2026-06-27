<?php

declare(strict_types=1);

namespace AzureOss\Identity;

/**
 * Defines how an Azure credential obtains access tokens.
 */
interface TokenCredential
{
    /**
     * Requests an access token for the supplied OAuth scopes.
     *
     * @throws CredentialUnavailableException
     * @throws AuthenticationFailedException
     */
    public function getToken(TokenRequestContext $context): AccessToken;
}
