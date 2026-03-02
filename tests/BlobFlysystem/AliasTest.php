<?php

declare(strict_types=1);

namespace AzureOss\Storage\Tests\BlobFlysystem;

use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AliasTest extends TestCase
{
    #[Test]
    #[RunInSeparateProcess]
    public function deprecated_alias_class_works_immediately(): void
    {
        $oldClass = 'AzureOss\FlysystemAzureBlobStorage\AzureBlobStorageAdapter';
        $newClass = AzureBlobStorageAdapter::class;

        // 1. Verify the alias class exists BEFORE we explicitly reference the new class name in a way that would trigger autoloading.
        // PHPUnit's separate process includes vendor/autoload.php, so our aliases.php should have run.
        self::assertTrue(
            class_exists($oldClass, false),
            "The deprecated alias class '$oldClass' should be registered immediately by aliases.php (without triggering autoloading of the new class)."
        );

        // 2. Use the old alias namespace to create an instance (without Azure server)
        $adapter = new $oldClass(
            new \AzureOss\Storage\Blob\BlobContainerClient(new \GuzzleHttp\Psr7\Uri('http://localhost/container')),
            'flysystem',
        );

        // 3. Verify the class is an instance of the new class name
        self::assertEquals($newClass, get_class($adapter));
    }
}
