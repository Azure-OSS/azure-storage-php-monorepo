<?php

declare(strict_types=1);

namespace AzureOss\Identity;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

/**
 * Authenticates a Microsoft Entra service principal with a client secret.
 */
final class ClientSecretCredential implements TokenCredential
{
    /**
     * @param  string  $tenantId  Microsoft Entra tenant ID.
     * @param  string  $clientId  Application (client) ID.
     * @param  string  $clientSecret  Application client secret.
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly ClientSecretCredentialOptions $options = new ClientSecretCredentialOptions,
    ) {}

    public function getToken(TokenRequestContext $context): AccessToken
    {
        try {
            $client = $this->options->httpClient ?? Psr18ClientDiscovery::find();
            $requestFactory = $this->options->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
            $streamFactory = $this->options->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        } catch (NotFoundException $e) {
            throw new \LogicException(
                'Unable to discover a PSR-18 HTTP client and/or PSR-17 factories. '
                .'Either provide TokenCredentialOptions::$httpClient/$requestFactory/$streamFactory or install compatible implementations (e.g. guzzlehttp/guzzle + guzzlehttp/psr7).',
                previous: $e,
            );
        }

        try {
            $url = "https://{$this->options->authorityHost}/{$this->tenantId}/oauth2/v2.0/token";
            $request = $requestFactory
                ->createRequest('POST', $url)
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

            $body = http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => implode(' ', $context->scopes),
            ], '', '&', PHP_QUERY_RFC3986);

            $request = $request->withBody($streamFactory->createStream($body));

            $response = $client->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                $status = $response->getStatusCode();
                $body = (string) $response->getBody();
                throw new AuthenticationFailedException("Failed to authenticate with Azure. HTTP {$status}: {$body}");
            }

            return AccessToken::fromTokenResponse((string) $response->getBody());
        } catch (\Throwable $e) {
            throw new AuthenticationFailedException('Failed to authenticate with Azure', previous: $e);
        }
    }
}
