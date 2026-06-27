---
sidebar_position: 2
title: Quickstart
---

This quickstart walks through the minimum flow: connect, create a container, upload a file, download it, and list blobs.

## Before You Start

Set your connection string:

```bash
export AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=...;AccountName=...;AccountKey=...;EndpointSuffix=core.windows.net"
```

## End-To-End Example

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Models\UploadBlobOptions;

$service = BlobServiceClient::fromConnectionString(
    getenv('AZURE_STORAGE_CONNECTION_STRING')
);

$container = $service->getContainerClient('quickstart');
$container->createIfNotExists();

$blob = $container->getBlobClient('hello.txt');

$blob->upload(
    'Hello from Azure-OSS',
    new UploadBlobOptions(contentType: 'text/plain')
);

$download = $blob->downloadStreaming();
$content = $download->content->getContents();

echo $content.PHP_EOL; // Hello from Azure-OSS

foreach ($container->getBlobs() as $item) {
    echo $item->name.PHP_EOL;
}

// Optional cleanup
$blob->deleteIfExists();
// $container->deleteIfExists();
```

## Next Steps

- Explore authorization options in the `Authorize` section.
- Explore container operations in `Containers`.
- Explore advanced blob operations in `Blobs`.
