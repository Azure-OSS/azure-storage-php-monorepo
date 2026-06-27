---
sidebar_position: 2
title: Download blobs
---

## Download A Blob (Streaming)

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('my-container')->getBlobClient('hello.txt');

$result = $blob->downloadStreaming();
```

## Read Content Into A String

```php
$content = $result->content->getContents();
```

## Access Downloaded Blob Properties

```php
$props = $result->properties;

echo $props->contentType.PHP_EOL;
echo $props->contentLength.PHP_EOL;
echo $props->lastModified->format(DATE_ATOM).PHP_EOL;
```
