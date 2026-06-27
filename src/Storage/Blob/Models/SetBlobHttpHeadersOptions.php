<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures set blob http headers options.
 */
final class SetBlobHttpHeadersOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
