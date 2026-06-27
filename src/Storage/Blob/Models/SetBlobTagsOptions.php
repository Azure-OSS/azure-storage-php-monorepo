<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures set blob tags options.
 */
final class SetBlobTagsOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
