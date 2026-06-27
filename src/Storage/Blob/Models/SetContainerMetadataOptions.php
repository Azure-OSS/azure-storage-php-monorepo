<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures set container metadata options.
 */
final class SetContainerMetadataOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
