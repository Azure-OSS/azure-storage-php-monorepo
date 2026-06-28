<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Blob\Unit;

use AzureOss\Storage\Blob\BlobClient;
use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\Models\BlobClientOptions;
use AzureOss\Storage\Blob\Models\BlobContainerClientOptions;
use AzureOss\Storage\Blob\Specialized\BlobLeaseClient;
use AzureOss\Storage\Common\Middleware\HttpClientOptions;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BlobLeaseClientOptionsTest extends TestCase
{
    #[Test]
    public function blob_lease_client_inherits_parent_http_options(): void
    {
        $blob = new BlobClient(
            new Uri('https://example.com/container/blob'),
            options: new BlobClientOptions($this->httpClientOptions()),
        );

        $this->assertHttpClientOptions($blob->getBlobLeaseClient());
    }

    #[Test]
    public function container_lease_client_inherits_parent_http_options(): void
    {
        $container = new BlobContainerClient(
            new Uri('https://example.com/container'),
            options: new BlobContainerClientOptions($this->httpClientOptions()),
        );

        $this->assertHttpClientOptions($container->getBlobLeaseClient());
    }

    private function httpClientOptions(): HttpClientOptions
    {
        return new HttpClientOptions(
            timeout: 123,
            connectTimeout: 45,
            verifySsl: false,
        );
    }

    private function assertHttpClientOptions(BlobLeaseClient $leaseClient): void
    {
        $clientProperty = new \ReflectionProperty($leaseClient, 'client');
        $client = $clientProperty->getValue($leaseClient);

        self::assertInstanceOf(Client::class, $client);

        $configProperty = new \ReflectionProperty($client, 'config');
        $config = $configProperty->getValue($client);

        self::assertIsArray($config);
        self::assertSame(123, $config['timeout']);
        self::assertSame(45, $config['connect_timeout']);
        self::assertFalse($config['verify']);
    }
}
