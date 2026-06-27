<?php

declare(strict_types=1);

namespace AzureOss\Identity;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Configures token credential options.
 */
class TokenCredentialOptions
{
    /**
     * @param  string  $authorityHost  Microsoft Entra authority host name without a scheme.
     * @param  ClientInterface|null  $httpClient  PSR-18 client, or null to use HTTP discovery.
     * @param  RequestFactoryInterface|null  $requestFactory  PSR-17 request factory, or null to use discovery.
     * @param  StreamFactoryInterface|null  $streamFactory  PSR-17 stream factory, or null to use discovery.
     */
    public function __construct(
        public readonly string $authorityHost = AzureAuthorityHosts::AZURE_PUBLIC_CLOUD,
        public readonly ?ClientInterface $httpClient = null,
        public readonly ?RequestFactoryInterface $requestFactory = null,
        public readonly ?StreamFactoryInterface $streamFactory = null,
    ) {}
}
