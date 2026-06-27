---
sidebar_position: 5
title: Delete blobs
---

## Delete A Blob

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('my-container')->getBlobClient('hello.txt');

$blob->delete();
```

## Delete If Exists

```php
$blob->deleteIfExists();
```

Use `deleteIfExists()` when you want idempotent cleanup behavior without handling not-found exceptions.
