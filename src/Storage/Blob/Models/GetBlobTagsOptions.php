<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures get blob tags options.
 */
final class GetBlobTagsOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
