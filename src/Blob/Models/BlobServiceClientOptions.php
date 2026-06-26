<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use AzureOss\Storage\Common\ApiVersion;
use AzureOss\Storage\Common\Middleware\HttpClientOptions;

final readonly class BlobServiceClientOptions
{
    public ApiVersion $apiVersion;

    public function __construct(
        public HttpClientOptions $httpClientOptions = new HttpClientOptions,
        ?ApiVersion $apiVersion = null,
    ) {
        $this->apiVersion = $apiVersion ?? ApiVersion::latestGA();
    }
}
