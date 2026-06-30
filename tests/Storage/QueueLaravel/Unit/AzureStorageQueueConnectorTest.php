<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\QueueLaravel\Unit;

use AzureOss\Storage\QueueLaravel\AzureStorageQueueConnector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AzureStorageQueueConnectorTest extends TestCase
{
    #[Test]
    public function connect_uses_the_queue_endpoint_from_the_connection_string(): void
    {
        $queue = (new AzureStorageQueueConnector)->connect([
            'connection_string' => 'QueueEndpoint=https://example.queue.core.windows.net/custom-path;SharedAccessSignature=sv=2024-08-04&sig=test',
            'queue' => 'jobs',
        ]);

        $queueClient = $queue->getQueueClient($queue->getQueue(null));

        self::assertSame('jobs', $queue->getQueue(null));
        self::assertSame('https://example.queue.core.windows.net/custom-path/jobs?sv=2024-08-04&sig=test', (string) $queueClient->uri);
    }

    #[Test]
    public function connect_builds_a_shared_key_endpoint_from_protocol_and_suffix(): void
    {
        $queue = (new AzureStorageQueueConnector)->connect([
            'protocol' => 'http',
            'endpoint_suffix' => 'example.test:10001',
            'account_name' => 'devstoreaccount1',
            'account_key' => 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
            'queue' => 'jobs',
        ]);

        $queueClient = $queue->getQueueClient($queue->getQueue(null));

        self::assertSame('http://devstoreaccount1.queue.example.test:10001/jobs', (string) $queueClient->uri);
    }

    #[Test]
    public function connect_prefers_an_explicit_queue_endpoint_when_provided(): void
    {
        $queue = (new AzureStorageQueueConnector)->connect([
            'queue_endpoint' => 'https://example.test/custom-account',
            'account_name' => 'example',
            'account_key' => 'Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==',
            'queue' => 'jobs',
        ]);

        $queueClient = $queue->getQueueClient($queue->getQueue(null));

        self::assertSame('https://example.test/custom-account/jobs', (string) $queueClient->uri);
    }
}
