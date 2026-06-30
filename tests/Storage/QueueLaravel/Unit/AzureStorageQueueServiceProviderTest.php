<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\QueueLaravel\Unit;

use AzureOss\Storage\QueueLaravel\AzureStorageQueue;
use AzureOss\Storage\QueueLaravel\AzureStorageQueueServiceProvider;
use Illuminate\Queue\QueueManager;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class AzureStorageQueueServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [AzureStorageQueueServiceProvider::class];
    }

    #[Test]
    public function it_registers_the_driver(): void
    {
        config([
            'queue.default' => 'azure',
            'queue.connections.azure' => [
                'driver' => 'azure-storage-queue',
                'connection_string' => 'UseDevelopmentStorage=true',
                'queue' => 'testing',
            ],
        ]);

        $app = $this->app;
        self::assertNotNull($app);
        /** @var QueueManager $manager */
        $manager = $app->make('queue');
        self::assertInstanceOf(AzureStorageQueue::class, $manager->connection('azure'));
    }
}
