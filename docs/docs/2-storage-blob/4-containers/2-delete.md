---
sidebar_position: 2
title: Delete a container
---

## Delete A Container

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$container = $service->getContainerClient('my-container');

$container->delete();
```

## Delete If Exists

Use this variant when you do not want an exception if the container does not exist:

```php
$container->deleteIfExists();
```
