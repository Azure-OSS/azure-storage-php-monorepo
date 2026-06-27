<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures sync copy from URI options.
 */
final class SyncCopyFromUriOptions
{
    public function __construct(
        public ?BlobRequestConditions $destinationConditions = null,
        public ?BlobRequestConditions $sourceConditions = null,
    ) {}
}
