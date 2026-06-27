---
sidebar_position: 2
title: Quickstart
---

## Prerequisites

- A storage account connection string
- An existing blob container (for example: `quickstart`)

If you want to authenticate without account keys (recommended for production), see [Microsoft Entra ID](../2-storage-blob/3-authorize/1-entra.md).

For local testing you can export:

```bash
export AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=...;AccountName=...;AccountKey=...;EndpointSuffix=core.windows.net"
export AZURE_STORAGE_CONTAINER="quickstart"
```

## Create The Filesystem

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;

$service = BlobServiceClient::fromConnectionString(
    getenv('AZURE_STORAGE_CONNECTION_STRING')
);

$container = $service->getContainerClient(
    getenv('AZURE_STORAGE_CONTAINER')
);

$adapter = new AzureBlobStorageAdapter($container);
$filesystem = new Filesystem($adapter);
```

## Basic Operations

```php
// Write
$filesystem->write('docs/hello.txt', 'Hello Azure Blob + Flysystem');

// Read
$contents = $filesystem->read('docs/hello.txt');

// Stream upload
$stream = fopen('/path/to/big-file.zip', 'r');
$filesystem->writeStream('archives/big-file.zip', $stream);
fclose($stream);

// List recursively
foreach ($filesystem->listContents('docs', true) as $item) {
    echo $item->path().PHP_EOL;
}

// Delete
$filesystem->delete('docs/hello.txt');
```

## Upload Options (HTTP Headers)

You can pass Azure upload options through the third argument of `write()` or `writeStream()`, including HTTP headers like `Cache-Control`:

```php
$css = 'body { color: #0f172a; }';

$filesystem->write(
    'assets/app.css',
    $css,
    [
        'httpHeaders' => [
            'cacheControl' => 'public, max-age=31536000',
            'contentType' => 'text/css',
        ],
    ]
);
```

Supported `httpHeaders` keys:

- `cacheControl`
- `contentDisposition`
- `contentEncoding`
- `contentHash`
- `contentLanguage`
- `contentType`

The adapter also supports these upload tuning keys:

- `initialTransferSize` (int)
- `maximumTransferSize` (int)
- `maximumConcurrency` (int)

## Conditional Writes

Pass conditions through the `conditions` write option. For example, the wildcard ETag can ensure that a write only succeeds when the file does not already exist:

```php
$filesystem->write('documents/report.json', $contents, [
    'conditions' => [
        'ifNoneMatch' => '*',
    ],
]);
```

Supported condition keys are `ifMatch`, `ifNoneMatch`, and `leaseId`.

For more advanced optimistic-concurrency scenarios, use the lower-level Blob client to retrieve an existing ETag and pass its string value as `ifMatch`. Lease IDs and lease lifecycle operations are also available through the lower-level Blob and lease clients. Conditional reads, deletes, and copies are not exposed by the Flysystem adapter.

## Public And Temporary URLs

By default, `publicUrl()` generates a long-lived SAS URL.  
If your container is publicly accessible, set `isPublicContainer: true` to return the direct blob URL.

```php
use League\Flysystem\Config;

$adapter = new AzureBlobStorageAdapter(
    $container,
    isPublicContainer: true
);

$directPublicUrl = $adapter->publicUrl('images/logo.png', new Config());
```

Temporary SAS URLs:

```php
use DateTimeImmutable;
use League\Flysystem\Config;

$temporaryUrl = $adapter->temporaryUrl(
    'exports/report.csv',
    new DateTimeImmutable('+15 minutes'),
    new Config(['permissions' => 'r'])
);
```

You can also override the response headers returned by the temporary URL:

```php
$temporaryUrl = $adapter->temporaryUrl(
    'exports/report.csv',
    new DateTimeImmutable('+15 minutes'),
    new Config([
        'permissions' => 'r',
        'httpHeaders' => [
            'cacheControl' => 'public, max-age=60',
            'contentDisposition' => 'attachment; filename="report.csv"',
            'contentEncoding' => 'identity',
            'contentLanguage' => 'en-US',
            'contentType' => 'text/csv',
        ],
    ])
);
```

Supported `httpHeaders` keys for temporary URLs:

- `cacheControl`
- `contentDisposition`
- `contentEncoding`
- `contentLanguage`
- `contentType`

## Notes

- Azure Blob Storage does not support Unix-style visibility metadata. Calling `setVisibility()` throws by default.
- Azure Blob Storage does not have real directories; directory operations are path-prefix based.
- SAS-based URLs (`temporaryUrl()` and non-public `publicUrl()`) require credentials that can sign SAS tokens.
