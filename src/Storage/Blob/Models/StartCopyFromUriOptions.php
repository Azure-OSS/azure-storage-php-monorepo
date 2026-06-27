<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures start copy from URI options.
 */
final class StartCopyFromUriOptions
{
    public function __construct(
        public ?BlobRequestConditions $destinationConditions = null,
        public ?BlobRequestConditions $sourceConditions = null,
    ) {}
}
