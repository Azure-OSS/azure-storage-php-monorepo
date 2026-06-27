<?php

declare(strict_types=1);

namespace AzureOss\Identity;

/**
 * Describes the OAuth scopes requested from a token credential.
 */
final class TokenRequestContext
{
    /**
     * @param  string[]  $scopes  OAuth scopes required by the operation.
     */
    public function __construct(
        public readonly array $scopes
    ) {}
}
