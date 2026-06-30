<?php

declare(strict_types=1);

namespace AzureOss\Tests\Identity\Unit;

use AzureOss\Identity\AuthenticationFailedException;
use AzureOss\Identity\CredentialUnavailableException;
use AzureOss\Identity\ManagedIdentityCredential;
use AzureOss\Identity\ManagedIdentityCredentialOptions;
use AzureOss\Identity\TokenRequestContext;
use AzureOss\Tests\Identity\Support\FakeHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

class ManagedIdentityCredentialTest extends TestCase
{
    /** @var list<string> */
    private const RELEVANT_ENV_VARS = [
        'AZURE_CLIENT_ID',
        'IDENTITY_ENDPOINT',
        'IDENTITY_HEADER',
        'IMDS_ENDPOINT',
        'MSI_ENDPOINT',
        'MSI_SECRET',
    ];

    /** @var array<string, string|false> */
    private array $originalEnv = [];

    /** @var list<string> */
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::RELEVANT_ENV_VARS as $name) {
            $this->originalEnv[$name] = getenv($name);
            $this->setEnvVar($name, null);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $name => $value) {
            $this->setEnvVar($name, $value === false ? null : $value);
        }

        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    #[Test]
    public function managed_identity_credential_requires_single_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ManagedIdentityCredential)->getToken(new TokenRequestContext([
            'https://graph.microsoft.com/.default',
            'https://vault.azure.net/.default',
        ]));
    }

    #[Test]
    public function imds_returns_token_and_sends_expected_request(): void
    {
        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('imds-token')),
        ]);

        $token = $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context('https://storage.azure.com/.default'));

        self::assertSame('imds-token', $token->token);

        $request = $this->singleRequest($client);

        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://localhost/imds/token', $this->requestPathWithoutQuery($request));
        self::assertSame('true', $request->getHeaderLine('Metadata'));
        self::assertSame('2018-02-01', $this->queryParams($request)['api-version'] ?? null);
        self::assertSame('https://storage.azure.com', $this->queryParams($request)['resource'] ?? null);
    }

    #[Test]
    public function imds_includes_client_id_from_options(): void
    {
        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('imds-token')),
        ]);

        $this->createCredential(
            $client,
            clientId: 'options-client',
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context());

        self::assertSame('options-client', $this->queryParams($this->singleRequest($client))['client_id'] ?? null);
    }

    #[Test]
    public function imds_includes_client_id_from_environment_when_options_missing(): void
    {
        $this->setEnvVars([
            'AZURE_CLIENT_ID' => 'env-client',
        ]);

        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('imds-token')),
        ]);

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context());

        self::assertSame('env-client', $this->queryParams($this->singleRequest($client))['client_id'] ?? null);
    }

    #[Test]
    public function imds_prefers_options_client_id_over_environment_client_id(): void
    {
        $this->setEnvVars([
            'AZURE_CLIENT_ID' => 'env-client',
        ]);

        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('imds-token')),
        ]);

        $this->createCredential(
            $client,
            clientId: 'options-client',
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context());

        self::assertSame('options-client', $this->queryParams($this->singleRequest($client))['client_id'] ?? null);
    }

    #[Test]
    public function imds_returns_credential_unavailable_when_endpoint_returns_404(): void
    {
        $client = new FakeHttpClient([
            new Response(404, [], 'not found'),
        ]);

        $this->expectException(CredentialUnavailableException::class);

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context());
    }

    #[Test]
    public function imds_returns_authentication_failed_when_endpoint_returns_non_success_status(): void
    {
        $client = new FakeHttpClient([
            new Response(500, [], 'boom'),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('HTTP 500');

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context());
    }

    #[Test]
    public function imds_returns_credential_unavailable_on_network_failure(): void
    {
        $request = (new HttpFactory)->createRequest('GET', 'http://localhost/imds/token');
        $client = new FakeHttpClient([
            new class($request) extends \RuntimeException implements NetworkExceptionInterface
            {
                public function __construct(private RequestInterface $request)
                {
                    parent::__construct('network failed');
                }

                public function getRequest(): RequestInterface
                {
                    return $this->request;
                }
            },
        ]);

        $this->expectException(CredentialUnavailableException::class);

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context());
    }

    #[Test]
    public function app_service_returns_token_and_sends_expected_request(): void
    {
        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/appservice/token',
            'IDENTITY_HEADER' => 'test-header',
        ]);

        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('app-token')),
        ]);

        $token = $this->createCredential($client, clientId: 'app-client')->getToken($this->context());

        self::assertSame('app-token', $token->token);

        $request = $this->singleRequest($client);

        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://localhost/appservice/token', $this->requestPathWithoutQuery($request));
        self::assertSame('test-header', $request->getHeaderLine('X-IDENTITY-HEADER'));
        self::assertSame('2019-08-01', $this->queryParams($request)['api-version'] ?? null);
        self::assertSame('https://storage.azure.com', $this->queryParams($request)['resource'] ?? null);
        self::assertSame('app-client', $this->queryParams($request)['client_id'] ?? null);
    }

    #[Test]
    public function app_service_throws_authentication_failed_on_non_200_response(): void
    {
        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/appservice/token',
            'IDENTITY_HEADER' => 'test-header',
        ]);

        $client = new FakeHttpClient([
            new Response(400, [], 'bad request'),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('HTTP 400');

        $this->createCredential($client)->getToken($this->context());
    }

    #[Test]
    public function legacy_msi_returns_token_and_uses_secret_header(): void
    {
        $this->setEnvVars([
            'MSI_ENDPOINT' => 'http://localhost/msi/token',
            'MSI_SECRET' => 'test-secret',
        ]);

        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('msi-token')),
        ]);

        $token = $this->createCredential($client, clientId: 'legacy-client')->getToken($this->context());

        self::assertSame('msi-token', $token->token);

        $request = $this->singleRequest($client);

        self::assertSame('GET', $request->getMethod());
        self::assertSame('http://localhost/msi/token', $this->requestPathWithoutQuery($request));
        self::assertSame('test-secret', $request->getHeaderLine('secret'));
        self::assertSame('2017-09-01', $this->queryParams($request)['api-version'] ?? null);
        self::assertSame('https://storage.azure.com', $this->queryParams($request)['resource'] ?? null);
        self::assertSame('legacy-client', $this->queryParams($request)['clientid'] ?? null);
        self::assertArrayNotHasKey('client_id', $this->queryParams($request));
    }

    #[Test]
    public function legacy_msi_omits_secret_header_when_secret_is_unset(): void
    {
        $this->setEnvVars([
            'MSI_ENDPOINT' => 'http://localhost/msi/token',
        ]);

        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('msi-token')),
        ]);

        $this->createCredential($client)->getToken($this->context());

        self::assertFalse($this->singleRequest($client)->hasHeader('secret'));
    }

    #[Test]
    public function legacy_msi_throws_authentication_failed_on_non_200_response(): void
    {
        $this->setEnvVars([
            'MSI_ENDPOINT' => 'http://localhost/msi/token',
        ]);

        $client = new FakeHttpClient([
            new Response(500, [], 'fail'),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('HTTP 500');

        $this->createCredential($client)->getToken($this->context());
    }

    #[Test]
    public function azure_arc_challenge_flow_reads_secret_file_and_retries(): void
    {
        $secretFile = $this->createTemporaryFile('secret-value');

        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/arc/token',
            'IMDS_ENDPOINT' => 'http://localhost/arc/imds',
        ]);

        $client = new FakeHttpClient([
            new Response(401, ['WWW-Authenticate' => 'Basic realm="'.$secretFile.'"']),
            new Response(200, [], $this->tokenResponseBody('arc-token')),
        ]);

        $token = $this->createCredential($client)->getToken($this->context());

        self::assertSame('arc-token', $token->token);
        self::assertCount(2, $client->requests);

        $firstRequest = $client->requests[0];
        $secondRequest = $client->requests[1];

        self::assertSame('true', $firstRequest->getHeaderLine('Metadata'));
        self::assertSame('2020-06-01', $this->queryParams($firstRequest)['api-version'] ?? null);
        self::assertSame('https://storage.azure.com', $this->queryParams($firstRequest)['resource'] ?? null);

        self::assertSame('true', $secondRequest->getHeaderLine('Metadata'));
        self::assertSame('Basic secret-value', $secondRequest->getHeaderLine('Authorization'));
        self::assertSame('2020-06-01', $this->queryParams($secondRequest)['api-version'] ?? null);
        self::assertSame('https://storage.azure.com', $this->queryParams($secondRequest)['resource'] ?? null);
    }

    #[Test]
    public function azure_arc_returns_token_immediately_on_200_response(): void
    {
        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/arc/token',
            'IMDS_ENDPOINT' => 'http://localhost/arc/imds',
        ]);

        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('arc-token')),
        ]);

        $token = $this->createCredential($client)->getToken($this->context());

        self::assertSame('arc-token', $token->token);
        self::assertCount(1, $client->requests);
    }

    #[Test]
    public function azure_arc_throws_authentication_failed_on_non_200_and_non_401_response(): void
    {
        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/arc/token',
            'IMDS_ENDPOINT' => 'http://localhost/arc/imds',
        ]);

        $client = new FakeHttpClient([
            new Response(403, [], 'forbidden'),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('HTTP 403');

        $this->createCredential($client)->getToken($this->context());
    }

    #[Test]
    public function azure_arc_throws_authentication_failed_when_challenge_header_is_invalid(): void
    {
        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/arc/token',
            'IMDS_ENDPOINT' => 'http://localhost/arc/imds',
        ]);

        $client = new FakeHttpClient([
            new Response(401, ['WWW-Authenticate' => 'Bearer realm="ignored"']),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('Missing or invalid WWW-Authenticate challenge');

        $this->createCredential($client)->getToken($this->context());
    }

    #[Test]
    public function azure_arc_throws_authentication_failed_when_secret_file_is_missing(): void
    {
        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/arc/token',
            'IMDS_ENDPOINT' => 'http://localhost/arc/imds',
        ]);

        $client = new FakeHttpClient([
            new Response(401, ['WWW-Authenticate' => 'Basic realm="/tmp/azure-arc-missing-secret"']),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('Failed to read Azure Arc secret file');

        $this->createCredential($client)->getToken($this->context());
    }

    #[Test]
    public function azure_arc_throws_authentication_failed_when_secret_file_is_empty(): void
    {
        $secretFile = $this->createTemporaryFile('');

        $this->setEnvVars([
            'IDENTITY_ENDPOINT' => 'http://localhost/arc/token',
            'IMDS_ENDPOINT' => 'http://localhost/arc/imds',
        ]);

        $client = new FakeHttpClient([
            new Response(401, ['WWW-Authenticate' => 'Basic realm="'.$secretFile.'"']),
        ]);

        $this->expectException(AuthenticationFailedException::class);
        $this->expectExceptionMessage('Azure Arc secret file is empty');

        $this->createCredential($client)->getToken($this->context());
    }

    #[Test]
    public function managed_identity_converts_storage_default_scope_to_resource(): void
    {
        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('scope-token')),
        ]);

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context('https://storage.azure.com/.default'));

        self::assertSame('https://storage.azure.com', $this->queryParams($this->singleRequest($client))['resource'] ?? null);
    }

    #[Test]
    public function managed_identity_converts_key_vault_default_scope_to_resource(): void
    {
        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('scope-token')),
        ]);

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context('https://vault.azure.net/.default'));

        self::assertSame('https://vault.azure.net', $this->queryParams($this->singleRequest($client))['resource'] ?? null);
    }

    #[Test]
    public function managed_identity_uses_scope_without_default_suffix_as_resource(): void
    {
        $client = new FakeHttpClient([
            new Response(200, [], $this->tokenResponseBody('scope-token')),
        ]);

        $this->createCredential(
            $client,
            imdsEndpoint: 'http://localhost/imds/token',
        )->getToken($this->context('https://management.azure.com'));

        self::assertSame('https://management.azure.com', $this->queryParams($this->singleRequest($client))['resource'] ?? null);
    }

    #[Test]
    public function managed_identity_rejects_an_empty_scope(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ManagedIdentityCredential)->getToken($this->context(''));
    }

    #[Test]
    public function managed_identity_rejects_zero_scopes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ManagedIdentityCredential)->getToken(new TokenRequestContext([]));
    }

    private function createCredential(FakeHttpClient $client, ?string $clientId = null, ?string $imdsEndpoint = null): ManagedIdentityCredential
    {
        return new ManagedIdentityCredential(new ManagedIdentityCredentialOptions(
            clientId: $clientId,
            httpClient: $client,
            requestFactory: new HttpFactory,
            imdsEndpoint: $imdsEndpoint,
        ));
    }

    private function context(string $scope = 'https://storage.azure.com/.default'): TokenRequestContext
    {
        return new TokenRequestContext([$scope]);
    }

    /**
     * @param  array<string, string|null>  $vars
     */
    private function setEnvVars(array $vars): void
    {
        foreach ($vars as $name => $value) {
            $this->setEnvVar($name, $value);
        }
    }

    private function setEnvVar(string $name, ?string $value): void
    {
        if ($value === null) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);

            return;
        }

        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    private function tokenResponseBody(string $token): string
    {
        $json = json_encode([
            'access_token' => $token,
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode token response.');
        }

        return $json;
    }

    /**
     * @return array<string, string>
     */
    private function queryParams(RequestInterface $request): array
    {
        $query = $request->getUri()->getQuery();
        if ($query === '') {
            return [];
        }

        parse_str($query, $params);

        $result = [];
        foreach ($params as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function requestPathWithoutQuery(RequestInterface $request): string
    {
        $uri = $request->getUri();
        $path = $uri->getScheme().'://'.$uri->getHost();
        if ($uri->getPort() !== null) {
            $path .= ':'.$uri->getPort();
        }

        return $path.$uri->getPath();
    }

    private function singleRequest(FakeHttpClient $client): RequestInterface
    {
        self::assertCount(1, $client->requests);

        return $client->requests[0];
    }

    private function createTemporaryFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'azure-arc-');
        self::assertIsString($path);

        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
