---
sidebar_position: 9
slug: /storage-queue-laravel/migrate-from-squigg-azure-queue-laravel
title: Migrate from squigg/azure-queue-laravel
description: A Laravel queue migration guide focused on config mapping, connection strategy, and worker behavior.
---

`azure-oss/storage-queue-laravel` is the replacement for `squigg/azure-queue-laravel`.

At first glance this looks like a config migration. In practice it is a queue-behavior migration wrapped in a config migration, and you will have a much better time if you treat it that way from the start.

## What changes

| Area | Old package | `azure-oss/storage-queue-laravel` |
| --- | --- | --- |
| Driver name | `azure` | `azure-storage-queue` |
| SDK underneath | `microsoft/azure-storage-queue` | `azure-oss/storage-queue` |
| Shared-key fields | `accountname`, `key` | `account_name`, `account_key` |
| Worker timing field | `timeout` | `retry_after` |
| Connection string support | Manual assembly | Native `connection_string` support |
| Additional queue options | Minimal | `time_to_live`, `create_queue`, `after_commit` |

## A migration sequence that reduces surprises

### 1. Replace the package

```bash
composer remove squigg/azure-queue-laravel
composer require azure-oss/storage-queue-laravel
```

### 2. Rename the driver

Replace:

```php
'driver' => 'azure',
```

with:

```php
'driver' => 'azure-storage-queue',
```

### 3. Decide whether you want explicit fields or a connection string

If your team already stores an Azure Storage connection string securely, that is often the cleanest first migration:

```php
'azure' => [
    'driver' => 'azure-storage-queue',
    'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
    'queue' => env('AZURE_STORAGE_QUEUE', 'default'),
    'retry_after' => 60,
],
```

If you want explicit fields instead, use:

```php
'azure' => [
    'driver' => 'azure-storage-queue',
    'account_name' => env('AZURE_QUEUE_STORAGE_NAME'),
    'account_key' => env('AZURE_QUEUE_KEY'),
    'protocol' => 'https',
    'endpoint_suffix' => env('AZURE_QUEUE_ENDPOINTSUFFIX', 'core.windows.net'),
    'queue_endpoint' => env('AZURE_QUEUE_ENDPOINT'),
    'queue' => env('AZURE_QUEUE_NAME', 'default'),
    'retry_after' => 60,
    'time_to_live' => null,
    'create_queue' => false,
],
```

Field mapping from the old package:

- `accountname` -> `account_name`
- `key` -> `account_key`
- `timeout` -> `retry_after`
- `endpoint` -> `endpoint_suffix`

### 4. Treat `retry_after` as an application behavior setting

This is the most important field to validate after the migration.

Make sure `retry_after` is longer than the slowest real job you expect to run, otherwise messages can become visible again while work is still in progress.

### 5. Re-test the operational cases, not just dispatch

Verify:

- long-running jobs
- failed jobs
- retries
- delayed jobs
- local development with custom endpoints or emulator-style setups

If those behave correctly, the migration is usually sound.

## What gets better

- a maintained Queue SDK underneath the Laravel connector
- first-class connection string support
- config names that are easier to read and support
- queue features that line up better with modern Laravel expectations

## Migration checklist

- Replace the package
- Rename the driver
- Choose connection string or explicit field config
- Map `timeout` to `retry_after`
- Re-test worker timing and retry behavior carefully

## Keep reading

- [Laravel Queue installation](./1-installation.md)
- [Laravel Queue quickstart](./2-quickstart.md)
- [A Modern Azure Queue Driver for Laravel](/blog/modern-azure-queue-for-laravel)
