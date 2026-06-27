---
sidebar_position: 7
title: Tag and find blobs
---

Blob index tags let you attach key/value tags and query blobs by SQL-like filters.

## Set And Get Blob Tags

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('my-container')->getBlobClient('invoices/2026-03.pdf');

$blob->upload('...');
$blob->setTags([
    'type' => 'invoice',
    'year' => '2026',
]);

$tags = $blob->getTags();
```

## Find Blobs By Tag (Account Scope)

```php
foreach ($service->findBlobsByTag("type = 'invoice' AND year = '2026'") as $result) {
    echo $result->containerName.'/'.$result->name.PHP_EOL;
}
```

## Find Blobs By Tag (Container Scope)

```php
$container = $service->getContainerClient('my-container');

foreach ($container->findBlobsByTag("type = 'invoice'") as $result) {
    echo $result->name.PHP_EOL;
}
```
