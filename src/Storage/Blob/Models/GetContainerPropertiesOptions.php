<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures get container properties options.
 */
final class GetContainerPropertiesOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
