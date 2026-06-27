<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures create container options.
 */
final class CreateContainerOptions
{
    public function __construct(
        public PublicAccessType $publicAccessType = PublicAccessType::NONE,
    ) {}
}
