<?php

declare(strict_types=1);

namespace AzureOss\Identity;

use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @experimental
 *
 * This credential is difficult to test reliably in CI because most managed identity
 * endpoints are only available from within the corresponding Azure runtime (IMDS,
 * App Service/Functions, Arc, etc.). If you try this in a real environment and it
 * works (or fails), please let us know which environment you used and any relevant
 * HTTP status/error details.
 */
final class ManagedIdentityCredential implements TokenCredential
{
    private const IMDS_ENDPOINT = 'http://169.254.169.254/metadata/identity/oauth2/token';

    private const IMDS_API_VERSION = '2018-02-01';

    private const APP_SERVICE_API_VERSION = '2019-08-01';

    private const LEGACY_MSI_API_VERSION = '2017-09-01';

    private const ARC_API_VERSION = '2020-06-01';

    public function __construct(
        private ManagedIdentityCredentialOptions $options = new ManagedIdentityCredentialOptions
    ) {}

    public function getToken(TokenRequestContext $context): AccessToken
    {
        $resource = $this->scopeToResource($context);
        $clientId = $this->options->clientId ?? $this->getStringEnv('AZURE_CLIENT_ID');

        $identityEndpoint = $this->getStringEnv('IDENTITY_ENDPOINT');
        $identityHeader = $this->getStringEnv('IDENTITY_HEADER');
        $imdsEndpoint = $this->getStringEnv('IMDS_ENDPOINT');
        $msiEndpoint = $this->getStringEnv('MSI_ENDPOINT');
        $msiSecret = $this->getStringEnv('MSI_SECRET');

        $environment = $this->detectEnvironment($identityEndpoint, $identityHeader, $imdsEndpoint, $msiEndpoint);

        if ($environment === 'app_service') {
            if ($identityEndpoint === null || $identityHeader === null) {
                throw new \LogicException('ManagedIdentityCredential environment detection error (app_service).');
            }

            return $this->getTokenFromAppService($resource, $identityEndpoint, $identityHeader, $clientId);
        }

        if ($environment === 'azure_arc') {
            if ($identityEndpoint === null) {
                throw new \LogicException('ManagedIdentityCredential environment detection error (azure_arc).');
            }

            return $this->getTokenFromAzureArc($resource, $identityEndpoint);
        }

        if ($environment === 'legacy_msi') {
            if ($msiEndpoint === null) {
                throw new \LogicException('ManagedIdentityCredential environment detection error (legacy_msi).');
            }

            return $this->getTokenFromMsiEndpoint($resource, $msiEndpoint, $msiSecret, $clientId);
        }

        return $this->getTokenFromImds($resource, $clientId);
    }

    private function getTokenFromImds(string $resource, ?string $clientId): AccessToken
    {
        try {
            $client = $this->options->httpClient ?? Psr18ClientDiscovery::find();
            $requestFactory = $this->options->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        } catch (NotFoundException $e) {
            throw new \LogicException(
                'Unable to discover a PSR-18 HTTP client and/or PSR-17 factories. '
                .'Either provide TokenCredentialOptions::$httpClient/$requestFactory or install compatible implementations (e.g. guzzlehttp/guzzle + guzzlehttp/psr7).',
                previous: $e,
            );
        }

        try {
            $url = self::appendQuery(self::IMDS_ENDPOINT, array_filter([
                'api-version' => self::IMDS_API_VERSION,
                'resource' => $resource,
                'client_id' => $clientId,
            ], static fn ($value) => $value !== null));

            $request = $requestFactory->createRequest('GET', $url)->withHeader('Metadata', 'true');
            $response = $client->sendRequest($request);

            return $this->handleImdsResponse($response);
        } catch (NetworkExceptionInterface $e) {
            throw new CredentialUnavailableException('Managed identity authentication unavailable. No response from the IMDS endpoint.', previous: $e);
        } catch (ClientExceptionInterface $e) {
            throw new AuthenticationFailedException('Failed to authenticate using managed identity (IMDS).', previous: $e);
        } catch (\Throwable $e) {
            throw new AuthenticationFailedException('Failed to authenticate using managed identity (IMDS).', previous: $e);
        }
    }

    private function handleImdsResponse(ResponseInterface $response): AccessToken
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status === 200) {
            return AccessToken::fromTokenResponse($body);
        }

        if ($status === 404) {
            throw new CredentialUnavailableException('Managed identity authentication unavailable. The IMDS endpoint returned 404.');
        }

        throw new AuthenticationFailedException("Failed to authenticate using managed identity (IMDS). HTTP {$status}: {$body}");
    }

    private function getTokenFromAppService(string $resource, string $identityEndpoint, string $identityHeader, ?string $clientId): AccessToken
    {
        [$client, $requestFactory] = $this->discoverClientAndRequestFactory();

        $response = $this->requestToken(
            $client,
            $requestFactory,
            $identityEndpoint,
            [
                'X-IDENTITY-HEADER' => $identityHeader,
            ],
            array_filter([
                'api-version' => self::APP_SERVICE_API_VERSION,
                'resource' => $resource,
                'client_id' => $clientId,
            ], static fn ($value) => $value !== null),
            'App Service managed identity',
        );

        return AccessToken::fromTokenResponse((string) $response->getBody());
    }

    private function getTokenFromMsiEndpoint(string $resource, string $msiEndpoint, ?string $msiSecret, ?string $clientId): AccessToken
    {
        [$client, $requestFactory] = $this->discoverClientAndRequestFactory();

        $headers = [];
        if (is_string($msiSecret)) {
            $headers['secret'] = $msiSecret;
        }

        $response = $this->requestToken(
            $client,
            $requestFactory,
            $msiEndpoint,
            $headers,
            array_filter([
                'api-version' => self::LEGACY_MSI_API_VERSION,
                'resource' => $resource,
                'clientid' => $clientId,
            ], static fn ($value) => $value !== null),
            'Managed identity',
        );

        return AccessToken::fromTokenResponse((string) $response->getBody());
    }

    private function getTokenFromAzureArc(string $resource, string $identityEndpoint): AccessToken
    {
        [$client, $requestFactory] = $this->discoverClientAndRequestFactory();

        try {
            $url = self::appendQuery($identityEndpoint, [
                'api-version' => self::ARC_API_VERSION,
                'resource' => $resource,
            ]);

            $request = $requestFactory->createRequest('GET', $url)->withHeader('Metadata', 'true');
            $response = $client->sendRequest($request);
        } catch (NetworkExceptionInterface $e) {
            throw new CredentialUnavailableException('Managed identity authentication unavailable. No response from the Azure Arc endpoint.', previous: $e);
        } catch (ClientExceptionInterface $e) {
            throw new AuthenticationFailedException('Failed to authenticate using managed identity (Azure Arc).', previous: $e);
        } catch (\Throwable $e) {
            throw new AuthenticationFailedException('Failed to authenticate using managed identity (Azure Arc).', previous: $e);
        }

        if ($response->getStatusCode() === 200) {
            return AccessToken::fromTokenResponse((string) $response->getBody());
        }

        if ($response->getStatusCode() !== 401) {
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
            throw new AuthenticationFailedException("Failed to authenticate using managed identity (Azure Arc). HTTP {$status}: {$body}");
        }

        $wwwAuthenticate = $response->getHeaderLine('WWW-Authenticate');
        $secretPath = $this->parseWwwAuthenticateBasicRealm($wwwAuthenticate);
        if ($secretPath === null) {
            throw new AuthenticationFailedException('Failed to authenticate using managed identity (Azure Arc). Missing or invalid WWW-Authenticate challenge.');
        }

        $secret = $this->readSecretFile($secretPath);

        $authResponse = $this->requestToken(
            $client,
            $requestFactory,
            $identityEndpoint,
            [
                'Metadata' => 'true',
                'Authorization' => "Basic {$secret}",
            ],
            [
                'api-version' => self::ARC_API_VERSION,
                'resource' => $resource,
            ],
            'Azure Arc managed identity',
        );

        return AccessToken::fromTokenResponse((string) $authResponse->getBody());
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $query
     */
    private function requestToken(ClientInterface $client, RequestFactoryInterface $requestFactory, string $url, array $headers, array $query, string $source): ResponseInterface
    {
        try {
            $url = self::appendQuery($url, $query);

            $request = $requestFactory->createRequest('GET', $url);
            foreach ($headers as $name => $value) {
                $request = $request->withHeader($name, $value);
            }

            $response = $client->sendRequest($request);
        } catch (NetworkExceptionInterface $e) {
            throw new CredentialUnavailableException("Managed identity authentication unavailable. No response from the {$source} endpoint.", previous: $e);
        } catch (ClientExceptionInterface $e) {
            throw new AuthenticationFailedException("Failed to authenticate using {$source}.", previous: $e);
        } catch (\Throwable $e) {
            throw new AuthenticationFailedException("Failed to authenticate using {$source}.", previous: $e);
        }

        if ($response->getStatusCode() !== 200) {
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
            throw new AuthenticationFailedException("Failed to authenticate using {$source}. HTTP {$status}: {$body}");
        }

        return $response;
    }

    /**
     * @return array{0: ClientInterface, 1: RequestFactoryInterface}
     */
    private function discoverClientAndRequestFactory(): array
    {
        try {
            $client = $this->options->httpClient ?? Psr18ClientDiscovery::find();
            $requestFactory = $this->options->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        } catch (NotFoundException $e) {
            throw new \LogicException(
                'Unable to discover a PSR-18 HTTP client and/or PSR-17 factories. '
                .'Either provide TokenCredentialOptions::$httpClient/$requestFactory or install compatible implementations (e.g. guzzlehttp/guzzle + guzzlehttp/psr7).',
                previous: $e,
            );
        }

        return [$client, $requestFactory];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function appendQuery(string $url, array $query): string
    {
        if ($query === []) {
            return $url;
        }

        $encoded = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if ($encoded === '') {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').$encoded;
    }

    private function scopeToResource(TokenRequestContext $context): string
    {
        if (count($context->scopes) !== 1) {
            throw new \InvalidArgumentException('ManagedIdentityCredential only supports a single scope.');
        }

        $scope = $context->scopes[0];
        if ($scope === '') {
            throw new \InvalidArgumentException('Invalid scope provided.');
        }

        $defaultSuffix = '/.default';
        if (str_ends_with($scope, $defaultSuffix)) {
            return substr($scope, 0, -strlen($defaultSuffix));
        }

        return $scope;
    }

    private function detectEnvironment(?string $identityEndpoint, ?string $identityHeader, ?string $imdsEndpoint, ?string $msiEndpoint): string
    {
        if ($identityEndpoint !== null && $identityHeader !== null) {
            return 'app_service';
        }

        if ($identityEndpoint !== null && $imdsEndpoint !== null) {
            return 'azure_arc';
        }

        if ($msiEndpoint !== null) {
            return 'legacy_msi';
        }

        return 'imds';
    }

    private function parseWwwAuthenticateBasicRealm(string $header): ?string
    {
        if ($header === '') {
            return null;
        }

        if (preg_match('/Basic\\s+realm="?(?<realm>[^",]+)"?/i', $header, $matches) !== 1) {
            return null;
        }

        return $matches['realm'];
    }

    private function readSecretFile(string $path): string
    {
        $secret = @file_get_contents($path);
        if ($secret === false) {
            throw new AuthenticationFailedException("Failed to read Azure Arc secret file: {$path}");
        }

        $secret = trim($secret);
        if ($secret === '') {
            throw new AuthenticationFailedException("Azure Arc secret file is empty: {$path}");
        }

        return $secret;
    }

    private function getStringEnv(string $name): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return null;
        }

        return $value;
    }
}
