<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures stage block options.
 */
final class StageBlockOptions
{
    public function __construct(
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
