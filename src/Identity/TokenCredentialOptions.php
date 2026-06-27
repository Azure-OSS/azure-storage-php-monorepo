<?php

declare(strict_types=1);

namespace AzureOss\Identity;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class TokenCredentialOptions
{
    public function __construct(
        public readonly string $authorityHost = AzureAuthorityHosts::AZURE_PUBLIC_CLOUD,
        public readonly ?ClientInterface $httpClient = null,
        public readonly ?RequestFactoryInterface $requestFactory = null,
        public readonly ?StreamFactoryInterface $streamFactory = null,
    ) {}
}
