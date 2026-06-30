<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use AzureOss\Storage\Queue\Exceptions\InvalidConnectionStringException;
use AzureOss\Storage\Queue\QueueServiceClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueueServiceClientTest extends TestCase
{
    #[Test]
    public function from_connection_string_supports_sas_endpoints(): void
    {
        $service = QueueServiceClient::fromConnectionString(
            'QueueEndpoint=https://example.queue.core.windows.net/;SharedAccessSignature=sv=2024-08-04&sig=test-signature'
        );

        self::assertSame('https://example.queue.core.windows.net/?sv=2024-08-04&sig=test-signature', (string) $service->uri);
        self::assertNull($service->credential);
    }

    #[Test]
    public function from_connection_string_supports_shared_key_endpoints(): void
    {
        $service = QueueServiceClient::fromConnectionString(
            'QueueEndpoint=https://example.queue.core.windows.net/;AccountName=example;AccountKey='
            .'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw=='
        );

        self::assertSame('https://example.queue.core.windows.net/', (string) $service->uri);
        self::assertInstanceOf(StorageSharedKeyCredential::class, $service->credential);
    }

    #[Test]
    public function from_connection_string_requires_a_usable_queue_endpoint_and_credential(): void
    {
        $this->expectException(InvalidConnectionStringException::class);

        QueueServiceClient::fromConnectionString('DefaultEndpointsProtocol=https;AccountName=example');
    }

    #[Test]
    public function get_queue_client_appends_the_queue_name_and_reuses_the_service_credential(): void
    {
        $service = QueueServiceClient::fromConnectionString('UseDevelopmentStorage=true');

        $queue = $service->getQueueClient('testing');

        self::assertEquals($service->credential, $queue->credential);
        self::assertSame('http://127.0.0.1:10001/devstoreaccount1/testing', (string) $queue->uri);
    }
}
