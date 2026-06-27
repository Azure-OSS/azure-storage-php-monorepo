<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

final class SetBlobMetadataOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
