<?php

declare(strict_types=1);

namespace AzureOss\Identity;

interface TokenCredential
{
    /**
     * @throws CredentialUnavailableException
     * @throws AuthenticationFailedException
     */
    public function getToken(TokenRequestContext $context): AccessToken;
}
