---
sidebar_position: 3
slug: /migration-guides/matthewbdaly-laravel-azure-storage
title: Migrate from matthewbdaly/laravel-azure-storage
description: A Laravel-focused migration from the older Azure Blob disk to the maintained azure-oss stack.
---

`azure-oss/storage-blob-laravel` is the replacement for `matthewbdaly/laravel-azure-storage`.

If you only remember one thing from this guide, make it this: the visible Laravel change is small, but the underlying stack changes completely. That is good news, as long as you migrate it in the right order.

## What changes

| Area | Old package | `azure-oss/storage-blob-laravel` |
| --- | --- | --- |
| Disk driver | `azure` | `azure-storage-blob` |
| Flysystem layer | Old League Azure adapter | `azure-oss/storage-blob-flysystem` |
| Blob SDK underneath | `microsoft/azure-storage-blob` | `azure-oss/storage-blob` |
| Shared-key config | `name`, `key`, `container` | `account_name`, `account_key`, `container` |
| Token-based auth | Not first-class | `client_secret`, `client_certificate`, `workload_identity`, `managed_identity` |
| URL controls | `url` | `url`, `temporary_url`, `is_public_container` |

## A safe migration path

### 1. Replace the package

```bash
composer remove matthewbdaly/laravel-azure-storage
composer require azure-oss/storage-blob-laravel
```

### 2. Change the driver name

Replace:

```php
'driver' => 'azure',
```

with:

```php
'driver' => 'azure-storage-blob',
```

### 3. Start with the simplest viable config

If you already have a working Azure Storage connection string, keep the first version of the new disk very small:

```php
'azure' => [
    'driver' => 'azure-storage-blob',
    'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
    'container' => env('AZURE_STORAGE_CONTAINER'),
    'url' => env('AZURE_STORAGE_URL'),
],
```

That gets you onto the new package without forcing an auth redesign on day one.

### 4. Translate the legacy shared-key fields if needed

Old config often looks like this:

```php
'azure' => [
    'driver' => 'azure',
    'name' => env('AZURE_STORAGE_NAME'),
    'key' => env('AZURE_STORAGE_KEY'),
    'container' => env('AZURE_STORAGE_CONTAINER'),
    'url' => env('AZURE_STORAGE_URL'),
    'prefix' => null,
    'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
],
```

The equivalent explicit shared-key form is:

```php
'azure' => [
    'driver' => 'azure-storage-blob',
    'credential' => 'shared_key',
    'account_name' => env('AZURE_STORAGE_ACCOUNT_NAME'),
    'account_key' => env('AZURE_STORAGE_ACCOUNT_KEY'),
    'container' => env('AZURE_STORAGE_CONTAINER'),
],
```

Field mapping:

- `name` -> `account_name`
- `key` -> `account_key`
- `sasToken` -> use a SAS-bearing `connection_string`

### 5. Re-test the parts Laravel apps notice first

Prioritize:

- `Storage::put()`
- `Storage::get()`
- `Storage::url()`
- `Storage::temporaryUrl()`
- any custom origin behavior through `url` or `temporary_url`

If those are stable, the migration is usually in very good shape.

### 6. Only then decide whether to modernize auth

Once the new disk is behaving in production, decide whether to keep:

- `connection_string`
- `shared_key`

or move forward to:

- `client_secret`
- `client_certificate`
- `workload_identity`
- `managed_identity`

That second phase is where the long-term security improvements really show up.

## What gets better

- one maintained Blob stack all the way from Laravel down to the SDK
- better support for modern Laravel versions
- a cleaner split between public URLs and signed temporary URLs
- stronger options for Azure-native authentication

## Migration checklist

- Replace the package
- Rename the disk driver to `azure-storage-blob`
- Prefer a connection string for the first rollout
- Map legacy shared-key fields if you do not use a connection string
- Re-test URL generation carefully
- Modernize auth only after the disk behavior is stable

## Keep reading

- [Laravel Blob installation](../4-storage-blob-laravel/1-installation.md)
- [Laravel Blob quickstart](../4-storage-blob-laravel/2-quickstart.md)
- [The Modern Laravel Blob Stack After matthewbdaly/laravel-azure-storage](../9-blog/3-laravel-azure-blob-storage-after-matthewbdaly.md)
