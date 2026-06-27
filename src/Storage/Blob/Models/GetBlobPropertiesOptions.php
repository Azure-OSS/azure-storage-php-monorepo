<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures get blob properties options.
 */
final class GetBlobPropertiesOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
