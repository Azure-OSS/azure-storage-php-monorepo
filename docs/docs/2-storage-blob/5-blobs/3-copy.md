---
sidebar_position: 3
title: Copy blobs
---

## Synchronous Copy

Use `syncCopyFromUri()` when you want copy completion before continuing.

Microsoft's `Copy Blob From URL` operation (the synchronous copy API) is limited to smaller source blobs:

> "copies a blob to a destination within the storage account synchronously for source blob sizes up to 256 mebibytes (MiB)."

If the source blob is larger than 256 MiB, Microsoft documents that the request fails with `409 (Conflict)`. For larger or longer-running copy operations, prefer asynchronous copy.

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Sas\BlobSasBuilder;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$container = $service->getContainerClient('my-container');

$sourceBlob = $container->getBlobClient('source.txt');
$targetBlob = $container->getBlobClient('target.txt');

$sourceSas = $sourceBlob->generateSasUri(
    BlobSasBuilder::new()
        ->setPermissions('r')
        ->setExpiresOn(new \DateTimeImmutable('+15 minutes'))
);

$copyResult = $targetBlob->syncCopyFromUri($sourceSas);
```

## Asynchronous Copy

```php
$copyResult = $targetBlob->startCopyFromUri($sourceSas);

// Poll until copy is complete
$targetBlob->waitForCopyCompletion();
```

## Copy In A Public Container

If source blobs are publicly readable, you can copy directly from the blob URI:

```php
$targetBlob->syncCopyFromUri($sourceBlob->uri);
```
