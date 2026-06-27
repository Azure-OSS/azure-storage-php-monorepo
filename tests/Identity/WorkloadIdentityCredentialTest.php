<?php

declare(strict_types=1);

namespace AzureOss\Identity\Tests;

use AzureOss\Identity\CredentialUnavailableException;
use AzureOss\Identity\TokenRequestContext;
use AzureOss\Identity\WorkloadIdentityCredential;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WorkloadIdentityCredentialTest extends TestCase
{
    /** @var array<string, string|false> */
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $names = [
            'AZURE_TENANT_ID',
            'AZURE_CLIENT_ID',
            'AZURE_FEDERATED_TOKEN_FILE',
        ];

        foreach ($names as $name) {
            $this->originalEnv[$name] = getenv($name);
        }

        putenv('AZURE_TENANT_ID=tenant');
        putenv('AZURE_CLIENT_ID=client');
    }

    protected function tearDown(): void
    {
        foreach ($this->originalEnv as $name => $value) {
            if ($value === false) {
                putenv($name);
            } else {
                putenv("{$name}={$value}");
            }
        }

        parent::tearDown();
    }

    #[Test]
    public function unavailable_when_token_file_is_missing(): void
    {
        putenv('AZURE_FEDERATED_TOKEN_FILE=/path/does/not/exist');

        $this->expectException(CredentialUnavailableException::class);

        (new WorkloadIdentityCredential)->getToken(new TokenRequestContext(['https://graph.microsoft.com/.default']));
    }

    #[Test]
    public function unavailable_when_token_file_is_empty(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'azure-wi-');
        self::assertIsString($path);

        file_put_contents($path, '');
        putenv("AZURE_FEDERATED_TOKEN_FILE={$path}");

        try {
            $this->expectException(CredentialUnavailableException::class);

            (new WorkloadIdentityCredential)->getToken(new TokenRequestContext(['https://graph.microsoft.com/.default']));
        } finally {
            @unlink($path);
        }
    }
}
