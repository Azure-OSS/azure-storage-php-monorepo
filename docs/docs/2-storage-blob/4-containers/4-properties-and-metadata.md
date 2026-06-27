---
sidebar_position: 4
title: Manage container properties and metadata
---

## Get Container Properties

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$container = $service->getContainerClient('my-container');

$properties = $container->getProperties();

echo $properties->lastModified->format(DATE_ATOM).PHP_EOL;
```

## Set Container Metadata

```php
$container->setMetadata([
    'project' => 'docs',
    'environment' => 'prod',
]);
```

## Read Metadata Back

```php
$metadata = $container->getProperties()->metadata;
```
