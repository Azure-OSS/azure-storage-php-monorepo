---
sidebar_position: 6
title: Blob properties and metadata
---

## Get properties

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('my-container')->getBlobClient('hello.txt');

$properties = $blob->getProperties();

echo $properties->contentType.PHP_EOL;
echo $properties->contentLength.PHP_EOL;
echo $properties->cacheControl.PHP_EOL;
```

## Get metadata

```php
$properties = $blob->getProperties();
print_r($properties->metadata);
```

## Set metadata

```php
$blob->setMetadata([
    'owner' => 'docs-team',
    'environment' => 'prod',
]);
```

## Set HTTP Headers

```php
use AzureOss\Storage\Blob\Models\BlobHttpHeaders;

$blob->setHttpHeaders(new BlobHttpHeaders(
    cacheControl: 'public, max-age=3600',
    contentType: 'text/plain',
    contentLanguage: 'en',
));
```

Common header fields:

- `cacheControl`
- `contentDisposition`
- `contentEncoding`
- `contentHash`
- `contentLanguage`
- `contentType`
