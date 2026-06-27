<?php

declare(strict_types=1);

namespace AzureOss\Identity;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ClientCertificateCredentialOptions extends TokenCredentialOptions
{
    public function __construct(
        string $authorityHost = AzureAuthorityHosts::AZURE_PUBLIC_CLOUD,
        public readonly bool $sendCertificateChain = false,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        parent::__construct($authorityHost, $httpClient, $requestFactory, $streamFactory);
    }
}
