---
sidebar_position: 1
title: Installation
---

`azure-oss/storage-queue-laravel` integrates Azure Storage Queues with Laravel's queue system.

## Requirements

- PHP 8.1+
- Laravel Queue (`illuminate/queue`) 10.x, 11.x, 12.x, or 13.x

## Install With Composer

```bash
composer require azure-oss/storage-queue-laravel
```

## Configure `config/queue.php`

Add a connection:

### Shared key (connection string)

```php
'connections' => [
    'azure' => [
        'driver' => 'azure-storage-queue',
        // Option A (recommended): connection string
        'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
        'queue' => env('AZURE_STORAGE_QUEUE', 'default'),
        'retry_after' => 60,
        'time_to_live' => null,
        'create_queue' => false,
    ],
],
```

### Shared key (account key)

```php
'azure' => [
    'driver' => 'azure-storage-queue',
    'account_name' => env('AZURE_QUEUE_STORAGE_NAME'),
    'account_key' => env('AZURE_QUEUE_KEY'),
    'protocol' => 'https',
    'endpoint_suffix' => env('AZURE_QUEUE_ENDPOINTSUFFIX', 'core.windows.net'),
    // optional for Azurite / custom endpoints:
    // 'queue_endpoint' => env('AZURE_QUEUE_ENDPOINT'),
    'queue' => env('AZURE_QUEUE_NAME', 'default'),
],
```

## Notes

- This connector supports shared key authentication (connection string / account key) and SAS-based authentication via `connection_string`.
- Set `create_queue => true` to automatically create the queue if it doesn't exist.

## Job expiration (important)

Azure Storage Queues uses a *visibility timeout* to hide a message while a worker is processing it. In this connector, you configure it via `retry_after`.

If your job takes longer than `retry_after`, the message can become visible again and another worker can pick it up, causing **double processing**.

Make sure `retry_after` is **greater than the longest expected runtime** of any job you process on this connection. See Laravel's docs on job expiration: https://laravel.com/docs/12.x/queues#job-expiration

## Next Step

Continue to [Quickstart](./quickstart) to dispatch jobs and run a worker using Azure Storage Queues.
