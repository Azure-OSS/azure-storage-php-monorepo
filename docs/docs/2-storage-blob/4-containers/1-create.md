---
sidebar_position: 1
title: Create a container
---

## Create A Container

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$container = $service->getContainerClient('my-container');

$container->create();
```

## Create If Not Exists

```php
$container->createIfNotExists();
```

## Create A Public Container

```php
use AzureOss\Storage\Blob\Models\CreateContainerOptions;
use AzureOss\Storage\Blob\Models\PublicAccessType;

$container->create(
    new CreateContainerOptions(publicAccessType: PublicAccessType::BLOB)
);
```

Available public access types:

- `PublicAccessType::NONE` (default)
- `PublicAccessType::BLOB`
- `PublicAccessType::CONTAINER`
