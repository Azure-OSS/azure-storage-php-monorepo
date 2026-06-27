---
sidebar_position: 3
title: List containers
---

## List All Containers

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));

foreach ($service->getBlobContainers() as $container) {
    echo $container->name.PHP_EOL;
}
```

## List Containers By Prefix

```php
foreach ($service->getBlobContainers('project-') as $container) {
    echo $container->name.PHP_EOL;
}
```
