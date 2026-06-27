<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use AzureOss\Storage\Common\ApiVersion;
use AzureOss\Storage\Common\Middleware\HttpClientOptions;

/**
 * Configures blob service client options.
 */
final readonly class BlobServiceClientOptions
{
    public function __construct(
        public HttpClientOptions $httpClientOptions = new HttpClientOptions,
        public ?ApiVersion $apiVersion = null,
    ) {}
}
