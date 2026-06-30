---
sidebar_position: 9
slug: /storage-file-share/migrate-from-microsoft-azure-storage-file
title: Migrate from microsoft/azure-storage-file
description: An honest migration guide for teams choosing between azure-oss/storage-file-share and a mounted Azure File Share.
---

If you are leaving `microsoft/azure-storage-file`, the first thing to know is that this is not the same kind of migration as Blob or Queue.

There is a modern PHP package in this ecosystem, `azure-oss/storage-file-share`, and it may absolutely be the right destination for your app. But for a meaningful slice of Azure Files workloads, the better answer is a mounted share over SMB or NFS rather than a PHP SDK abstraction.

That is the decision to make first.

## What changes

| Area | Legacy package | `azure-oss/storage-file-share` |
| --- | --- | --- |
| Main entry point | `FileRestProxy` | `ShareServiceClient` |
| Client model | One broad proxy | Share, directory, and file clients |
| PHP target | PHP `>=5.6` | PHP `^8.2` |
| Documented strength | General Azure Files SDK usage | Azure Files service access patterns and SAS generation |
| Migration shape | Legacy SDK | Partial replacement today |

## Choose your destination before you touch code

### Option 1: You mainly need SAS generation or Azure-aware service access

This is where `azure-oss/storage-file-share` is already a strong fit.

The package supports and documents:

- share clients
- directory clients
- file clients
- share and file SAS generation

If that is the center of your workload, the SDK path makes sense.

### Option 2: You mainly want ordinary filesystem behavior

If your app really wants to:

- read and write files through familiar file APIs
- move or rename paths like mounted storage
- treat Azure Files as infrastructure instead of application logic

then a mounted share is often the more natural endpoint for the migration.

That can feel less like an SDK upgrade and more like an architecture decision, because it is.

### Option 3: You rely heavily on `FileRestProxy` CRUD workflows

In that case, do a gap review before promising a one-step move.

Today the public `azure-oss/storage-file-share` docs are intentionally strongest around service access patterns and SAS generation. That is useful, but it is not the same thing as claiming feature-for-feature parity with every older `FileRestProxy` code path.

## If the SDK path is right, migrate like this

### 1. Replace the package

```bash
composer remove microsoft/azure-storage-file
composer require azure-oss/storage-file-share
```

### 2. Replace `FileRestProxy` with scoped clients

Old style:

```php
use MicrosoftAzure\Storage\File\FileRestProxy;

$fileClient = FileRestProxy::createFileService($connectionString);
```

New style:

```php
use AzureOss\Storage\File\Share\ShareServiceClient;

$service = ShareServiceClient::fromConnectionString($connectionString);
$file = $service
    ->getShareClient('documents')
    ->getDirectoryClient('reports')
    ->getFileClient('summary.txt');
```

Like the Blob and Queue migrations, the new model becomes clearer once you scope work through smaller clients instead of one large proxy.

### 3. Rebuild SAS generation around the new clients

```php
use AzureOss\Storage\File\Share\Sas\ShareFileSasPermissions;
use AzureOss\Storage\File\Share\Sas\ShareSasBuilder;

$sasUri = $file->generateSasUri(
    ShareSasBuilder::new()
        ->setPermissions(new ShareFileSasPermissions(read: true))
        ->setExpiresOn(new DateTimeImmutable('+15 minutes')),
);
```

### 4. Validate the workload before you call it migrated

Specifically check:

- whether SAS generation covers the real use cases you depend on
- whether your file and directory operations map cleanly to the current package
- whether a mounted share would actually simplify the design

## What is already better on the SDK path

- a modern PHP baseline
- client naming that aligns with the rest of the `azure-oss` packages
- first-class documentation for Azure Files SAS generation
- a healthier long-term home than the retired Microsoft package

## Migration checklist

- Decide whether your destination should be an SDK or a mounted share
- Replace `FileRestProxy` only if the SDK path matches your workload
- Rebuild SAS generation using the new client model
- Review CRUD expectations before promising parity

## Keep reading

- [File Share overview](./0-overview.md)
- [File Share installation](./1-installation.md)
- [Generating SAS URLs](./2-generating-sas-urls.md)
- [What to Use After microsoft/azure-storage-file](/blog/azure-file-share-after-microsoft-azure-storage-file)
