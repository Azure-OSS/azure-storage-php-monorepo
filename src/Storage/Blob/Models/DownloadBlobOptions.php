<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures download blob options.
 */
final class DownloadBlobOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
