---
sidebar_position: 1
title: Upload blobs
---

## Upload A String

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('my-container')->getBlobClient('hello.txt');

$blob->upload('Hello world');
```

## Upload A Stream

```php
$stream = fopen('/path/to/large-file.zip', 'r');
$blob->upload($stream);
fclose($stream);
```

## Upload With Transfer Options

```php
use AzureOss\Storage\Blob\Models\UploadBlobOptions;

$blob->upload(
    file_get_contents('/path/to/photo.jpg'),
    new UploadBlobOptions(
        initialTransferSize: 8_000_000, // 8 MB
        maximumTransferSize: 4_000_000, // 4 MB
        maximumConcurrency: 8,
    )
);
```

`initialTransferSize` and `maximumTransferSize` are byte sizes used for upload chunking:

- `initialTransferSize`: size of the first transfer attempt.
- `maximumTransferSize`: maximum size per chunk when the upload is split into multiple transfers.

## Set HTTP Headers

```php
use AzureOss\Storage\Blob\Models\UploadBlobOptions;
use AzureOss\Storage\Blob\Models\BlobHttpHeaders;

$blob->upload(
    'Hello world',
    new UploadBlobOptions(
        httpHeaders: new BlobHttpHeaders(
            cacheControl: 'public, max-age=3600',
            contentType: 'text/plain',
            contentLanguage: 'en',
            contentDisposition: 'inline',
            contentEncoding: 'utf-8',
        )
    )
);
```

You can also set or replace headers after upload:

```php
use AzureOss\Storage\Blob\Models\BlobHttpHeaders;

$blob->setHttpHeaders(new BlobHttpHeaders(
    cacheControl: 'public, max-age=3600',
    contentType: 'text/plain',
    contentLanguage: 'en',
    contentDisposition: 'inline',
    contentEncoding: 'utf-8',
));
```
