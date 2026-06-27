<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Defines the supported public access type values.
 */
enum PublicAccessType: string
{
    case NONE = 'none';
    case BLOB = 'blob';
    case CONTAINER = 'container';
}
