<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

final class CommitBlockListOptions
{
    public function __construct(
        public BlobHttpHeaders $httpHeaders = new BlobHttpHeaders,
    ) {
    }
}
