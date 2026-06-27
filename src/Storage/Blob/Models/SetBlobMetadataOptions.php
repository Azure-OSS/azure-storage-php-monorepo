<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures set blob metadata options.
 */
final class SetBlobMetadataOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
