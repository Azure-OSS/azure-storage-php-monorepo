---
sidebar_position: 9
slug: /storage-blob-flysystem/migrate-from-league-flysystem-azure-blob-storage
title: Migrate from league/flysystem-azure-blob-storage
description: A focused Flysystem migration from the abandoned Azure adapter to the modern azure-oss replacement.
---

`azure-oss/storage-blob-flysystem` is the package you want if you are moving off `league/flysystem-azure-blob-storage`.

This is usually the smoothest migration in the set because the Flysystem layer itself stays familiar. The real change is that you stop building on the legacy Microsoft Blob SDK and switch to a maintained Blob stack underneath the adapter.

## What actually changes

| Area | Old adapter | New adapter |
| --- | --- | --- |
| Package status | Abandoned | Current package |
| Blob SDK underneath | `microsoft/azure-storage-blob` | `azure-oss/storage-blob` |
| Adapter namespace | `League\\Flysystem\\AzureBlobStorage\\AzureBlobStorageAdapter` | `AzureOss\\Storage\\BlobFlysystem\\AzureBlobStorageAdapter` |
| Constructor input | `BlobRestProxy` plus container name | `BlobContainerClient` |
| URL behavior | Legacy adapter patterns | Modern SAS handling and public-container URL support |

## The key refactor to understand

Old setup:

```php
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

$client = BlobRestProxy::createBlobService($connectionString);
$adapter = new AzureBlobStorageAdapter($client, 'documents');
$filesystem = new Filesystem($adapter);
```

New setup:

```php
use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;

$service = BlobServiceClient::fromConnectionString($connectionString);
$container = $service->getContainerClient('documents');
$adapter = new AzureBlobStorageAdapter($container);
$filesystem = new Filesystem($adapter);
```

The adapter now receives a `BlobContainerClient`, which is a cleaner abstraction than handing it a broad service proxy and a free-floating container name.

## A practical migration sequence

### 1. Replace the package

```bash
composer remove league/flysystem-azure-blob-storage
composer require azure-oss/storage-blob-flysystem
```

### 2. Update the adapter import

Replace:

```php
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
```

with:

```php
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
```

### 3. Build a `BlobContainerClient`

The new adapter does not accept `BlobRestProxy`.

Create a `BlobServiceClient`, then derive the container client that your filesystem should be scoped to.

### 4. Re-test URL behavior before you call it done

Pay special attention to:

- temporary URLs
- public URLs
- public container handling
- response header overrides on signed URLs

These are usually the highest-value things to verify after the constructor update.

### 5. Revisit any implicit upload behavior

The new adapter exposes clearer write options around headers, conditions, and transfer behavior. If your previous setup relied on defaults you never documented, this is a good moment to make them explicit.

## What gets better

- a maintained adapter on top of a maintained Blob SDK
- a cleaner boundary through `BlobContainerClient`
- better alignment with the Laravel filesystem package
- clearer support for public URL generation and modern SAS flows

## Migration checklist

- Replace the package and namespace import
- Create a `BlobContainerClient` instead of a legacy proxy
- Re-test temporary and public URL behavior
- Re-test metadata, headers, and any conditional write paths

## Keep reading

- [Flysystem installation](./1-installation.md)
- [Flysystem quickstart](./2-quickstart.md)
- [Leaving league/flysystem-azure-blob-storage Behind](/blog/migrating-from-league-flysystem-azure-blob-storage)
