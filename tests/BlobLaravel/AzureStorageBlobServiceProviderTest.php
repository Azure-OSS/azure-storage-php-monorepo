<?php

declare(strict_types=1);

namespace AzureOss\Storage\Tests\BlobLaravel;

use AzureOss\Storage\BlobLaravel\AzureStorageBlobServiceProvider;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AzureStorageBlobServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [AzureStorageBlobServiceProvider::class];
    }

    #[Test]
    public function it_throws_when_container_is_missing(): void
    {
        /** @phpstan-ignore-next-line */
        $this->app['config']->set('filesystems.disks.azure', [
            'driver' => 'azure-storage-blob',
            'connection_string' => 'UseDevelopmentStorage=true',
        ]);

        Storage::forgetDisk('azure');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The [container] must be a string in the disk configuration.');

        Storage::disk('azure');
    }

    #[Test]
    public function it_throws_when_container_is_not_a_string(): void
    {
        /** @phpstan-ignore-next-line */
        $this->app['config']->set('filesystems.disks.azure', [
            'driver' => 'azure-storage-blob',
            'connection_string' => 'UseDevelopmentStorage=true',
            'container' => ['invalid'],
        ]);

        Storage::forgetDisk('azure');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The [container] must be a string in the disk configuration.');

        Storage::disk('azure');
    }
}
