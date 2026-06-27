<?php

declare(strict_types=1);

namespace AzureOss\Identity;

final class ChainedTokenCredential implements TokenCredential
{
    /**
     * @param  TokenCredential[]  $sources
     */
    public function __construct(
        private readonly array $sources = []
    ) {}

    public function getToken(TokenRequestContext $context): AccessToken
    {
        $unavailable = [];

        foreach ($this->sources as $source) {
            try {
                return $source->getToken($context);
            } catch (CredentialUnavailableException $e) {
                $unavailable[] = $e;

                continue;
            }
        }

        if ($unavailable !== []) {
            throw CredentialUnavailableException::createAggregateException('No credential available.', $unavailable);
        }

        throw new CredentialUnavailableException('No credential available.');
    }
}
