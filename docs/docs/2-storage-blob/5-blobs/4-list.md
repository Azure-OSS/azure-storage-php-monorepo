---
sidebar_position: 4
title: List blobs
---

## List All Blobs In A Container

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$container = $service->getContainerClient('my-container');

foreach ($container->getBlobs() as $blob) {
    echo $blob->name.PHP_EOL;
}
```

## List Blobs With A Prefix

```php
foreach ($container->getBlobs('images/') as $blob) {
    echo $blob->name.PHP_EOL;
}
```

## List Blobs By Hierarchy

```php
use AzureOss\Storage\Blob\Models\Blob;
use AzureOss\Storage\Blob\Models\BlobPrefix;

foreach ($container->getBlobsByHierarchy('images/') as $item) {
    if ($item instanceof Blob) {
        echo "blob: {$item->name}".PHP_EOL;
    } elseif ($item instanceof BlobPrefix) {
        echo "prefix: {$item->name}".PHP_EOL;
    }
}
```

## Control Page Size

```php
use AzureOss\Storage\Blob\Models\GetBlobsOptions;

$options = new GetBlobsOptions(pageSize: 100);

foreach ($container->getBlobs(options: $options) as $blob) {
    // ...
}
```
