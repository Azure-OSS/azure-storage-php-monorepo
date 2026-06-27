---
sidebar_position: 2
title: Quickstart
---

## Prerequisites

- `config/queue.php` contains an `azure` connection using `driver => azure-storage-queue`
- Your `.env` contains either `AZURE_STORAGE_CONNECTION_STRING` or `AZURE_QUEUE_STORAGE_NAME` + `AZURE_QUEUE_KEY`

Example `.env` values:

```bash
AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=...;AccountName=...;AccountKey=...;EndpointSuffix=core.windows.net"
AZURE_STORAGE_QUEUE="default"
```

## Dispatch A Job

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ExampleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Your job logic
    }
}
```

Dispatch it on the Azure connection:

```php
ExampleJob::dispatch()->onConnection('azure');
```

## Run The Worker

```bash
php artisan queue:work azure
```

## Job expiration (retry_after)

Set `retry_after` to a value higher than your longest-running jobs. If a job runs longer than `retry_after`, the message can reappear and be processed by another worker (double processing). See: https://laravel.com/docs/12.x/queues#job-expiration

## Per-message options

`pushRaw()` accepts `retry_after` and `time_to_live` options (seconds):

```php
$queue->pushRaw($payload, null, [
    'retry_after' => 10,
    'time_to_live' => 3600,
]);
```
