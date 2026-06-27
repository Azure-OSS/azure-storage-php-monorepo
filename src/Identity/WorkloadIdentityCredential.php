<?php

declare(strict_types=1);

namespace AzureOss\Identity;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

/**
 * Exchanges a federated workload identity token for a Microsoft Entra access token.
 */
final class WorkloadIdentityCredential implements TokenCredential
{
    public function __construct(
        private WorkloadIdentityCredentialOptions $options = new WorkloadIdentityCredentialOptions,
    ) {}

    public function getToken(TokenRequestContext $context): AccessToken
    {
        $tenantId = $this->options->tenantId ?? getenv('AZURE_TENANT_ID');
        $clientId = $this->options->clientId ?? getenv('AZURE_CLIENT_ID');
        $tokenFilePath = $this->options->tokenFilePath ?? getenv('AZURE_FEDERATED_TOKEN_FILE');

        if (! is_string($tenantId) || ! is_string($clientId) || ! is_string($tokenFilePath)) {
            throw new CredentialUnavailableException(
                'WorkloadIdentityCredential authentication unavailable. '
                .'The workload options are not fully configured. '
                .'Ensure tenantId, clientId, and tokenFilePath are provided via options or the '
                .'AZURE_TENANT_ID, AZURE_CLIENT_ID, and AZURE_FEDERATED_TOKEN_FILE environment variables.',
            );
        }

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
            $assertion = $this->readFederatedToken($tokenFilePath);

            $url = "https://{$this->options->authorityHost}/{$tenantId}/oauth2/v2.0/token";
            $request = $requestFactory
                ->createRequest('POST', $url)
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

            $body = http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
                'scope' => implode(' ', $context->scopes),
            ], '', '&', PHP_QUERY_RFC3986);

            $request = $request->withBody($streamFactory->createStream($body));

            $response = $client->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                $status = $response->getStatusCode();
                $body = (string) $response->getBody();
                throw new AuthenticationFailedException("Failed to authenticate with Azure using workload identity. HTTP {$status}: {$body}");
            }

            return AccessToken::fromTokenResponse((string) $response->getBody());
        } catch (CredentialUnavailableException|AuthenticationFailedException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AuthenticationFailedException('Failed to authenticate with Azure using workload identity', previous: $e);
        }
    }

    private function readFederatedToken(string $tokenFilePath): string
    {
        $token = @file_get_contents($tokenFilePath);
        if ($token === false) {
            throw new CredentialUnavailableException("Unable to read federated token file: {$tokenFilePath}");
        }

        $token = trim($token);
        if ($token === '') {
            throw new CredentialUnavailableException("Federated token file is empty: {$tokenFilePath}");
        }

        return $token;
    }
}
