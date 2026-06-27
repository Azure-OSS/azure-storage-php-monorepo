<?php

declare(strict_types=1);

namespace AzureOss\Identity;

final class TokenRequestContext
{
    /**
     * @param  string[]  $scopes
     */
    public function __construct(
        public readonly array $scopes
    ) {}
}
