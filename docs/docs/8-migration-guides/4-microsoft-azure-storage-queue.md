---
sidebar_position: 4
slug: /migration-guides/microsoft-azure-storage-queue
title: Migrate from microsoft/azure-storage-queue
description: A practical upgrade path from QueueRestProxy to the modern azure-oss Queue clients.
---

`azure-oss/storage-queue` is the package to adopt if your code still uses `microsoft/azure-storage-queue`.

Like the Blob migration, the headline change is the move away from a single `*RestProxy`. Unlike the Blob migration, the part most likely to bite you is not the API shape. It is queue behavior: invisibility windows, retries, and TTL assumptions.

## What changes

| Area | Legacy package | `azure-oss/storage-queue` |
| --- | --- | --- |
| Main entry point | `QueueRestProxy` | `QueueServiceClient` |
| Queue-scoped work | Queue name passed into methods | `QueueClient` |
| PHP target | PHP `>=5.6` | PHP `^8.2` |
| Auth | Connection strings, SAS-style endpoints | Connection strings, shared key, SAS, Microsoft Entra ID via `azure-oss/identity` |
| Local development | Legacy guidance | Current docs with Azurite coverage |

## The mental shift

Old setup:

```php
use MicrosoftAzure\Storage\Queue\QueueRestProxy;

$queueClient = QueueRestProxy::createQueueService($connectionString);
```

New setup:

```php
use AzureOss\Storage\Queue\QueueServiceClient;

$service = QueueServiceClient::fromConnectionString($connectionString);
$queue = $service->getQueueClient('jobs');
```

That smaller `QueueClient` is the important improvement. It lets the code talk to one queue with one object instead of passing queue names through every call.

## A migration plan that stays sane

### 1. Replace the package

```bash
composer remove microsoft/azure-storage-queue
composer require azure-oss/storage-queue
```

### 2. Keep the first auth path conservative

If your app already works with a connection string, start there again:

```php
$service = QueueServiceClient::fromConnectionString($connectionString);
```

That keeps the migration focused on the SDK behavior first.

### 3. Replace `QueueRestProxy` with scoped clients

Map the broad proxy model to the new structure:

- `QueueRestProxy` -> `QueueServiceClient`
- repeated queue name arguments -> `QueueClient`

### 4. Re-test visibility and timing behavior on purpose

Do not treat this as routine CRUD validation.

Explicitly verify:

- receive visibility timeout
- update visibility timeout
- message TTL assumptions
- delayed processing expectations
- concurrency behavior in your workers

Queue migrations are successful when workers behave predictably, not when the first `sendMessage()` call works.

### 5. Modernize auth later, separately

Once the queue behavior is stable, decide whether to keep connection strings or move toward:

- shared key credentials
- SAS-based access
- Microsoft Entra ID

Separate those concerns and the rollout gets much easier to debug.

## What gets better

- clearer service and queue boundaries in the code
- a Queue SDK that fits the rest of the `azure-oss` ecosystem
- current docs and Azurite guidance
- a better long-term auth story through `azure-oss/identity`

## Migration checklist

- Replace the package
- Swap `QueueRestProxy` for `QueueServiceClient`
- Introduce `QueueClient` where queue names were repeatedly passed around
- Re-test invisibility, retries, and TTL behavior
- Only then evaluate auth modernization

## Keep reading

- [Queue overview](../5-storage-queue/0-overview.md)
- [Queue installation](../5-storage-queue/1-installation.md)
- [Queue quickstart](../5-storage-queue/2-quickstart.md)
- [A Modern Azure Queue Driver for Laravel](../9-blog/4-modern-azure-queue-for-laravel.md)
