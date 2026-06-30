---
sidebar_position: 1
slug: /migration-guides/microsoft-azure-storage-blob
title: Migrate from microsoft/azure-storage-blob
description: A practical path from BlobRestProxy to the modern azure-oss Blob client model.
---

`azure-oss/storage-blob` is the package to move to if your current application still depends on `microsoft/azure-storage-blob`.

This is a very doable migration, but it is not a search-and-replace exercise. The big change is architectural: you move from one broad proxy object to a set of smaller clients that match how Blob Storage is actually shaped.

## What changes, at a glance

| Area | Legacy package | `azure-oss/storage-blob` |
| --- | --- | --- |
| Main entry point | `BlobRestProxy` | `BlobServiceClient` |
| How work is scoped | Pass container and blob names into methods | Narrow from service to container to blob clients |
| PHP target | PHP `>=5.6` | PHP `^8.2` |
| Auth | Connection strings and SAS-style endpoints | Connection strings, shared key, SAS, Microsoft Entra ID via `azure-oss/identity` |
| Documentation focus | Legacy SDK usage | Modern Blob features, SAS, tags, versions, leases, Azurite |

## The mental-model shift to get right

Old code usually starts here:

```php
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

$blobClient = BlobRestProxy::createBlobService($connectionString);
```

New code starts one layer higher, then narrows with intent:

```php
use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString($connectionString);
$container = $service->getContainerClient('documents');
$blob = $container->getBlobClient('report.pdf');
```

That is the core idea of the migration. Once you stop thinking "one proxy, many method calls" and start thinking "service, then container, then blob", the rest falls into place much faster.

## A low-risk migration plan

### 1. Replace the package

```bash
composer remove microsoft/azure-storage-blob
composer require azure-oss/storage-blob
```

### 2. Keep auth boring on the first pass

If the current app works with a connection string, keep using a connection string first:

```php
$service = BlobServiceClient::fromConnectionString($connectionString);
```

That keeps the first deployment focused on the client API shift instead of mixing in an auth redesign.

### 3. Replace repeated names with scoped clients

Old style:

```php
$blobClient->createBlockBlob($containerName, $blobName, $contents);
```

New style:

```php
$service = BlobServiceClient::fromConnectionString($connectionString);
$container = $service->getContainerClient($containerName);
$blob = $container->getBlobClient($blobName);

$blob->upload($contents);
```

If you see the same container name threaded through a file again and again, that is your sign to introduce a `BlobContainerClient`.

### 4. Re-test every signed access path

Blob migrations often look successful until the first generated URL hits production.

Verify:

- blob SAS URLs
- container SAS URLs
- account-level SAS workflows, if you use them
- response header overrides on signed links
- any snapshot- or version-specific URL behavior

### 5. Only modernize auth after the SDK move is stable

Once the package migration is settled, decide whether you want to move beyond connection strings toward:

- shared key credentials
- Microsoft Entra ID
- workload identity
- managed identity

Those are good upgrades. They are just easier to reason about after the client migration is already done.

## What tends to get better immediately

- code that reflects the actual shape of Blob Storage
- cleaner boundaries between service, container, and blob operations
- better docs for tags, versions, leases, listing, and SAS generation
- a path into the Flysystem and Laravel packages that use the same Blob foundation
- clearer local development guidance through Azurite

## Migration checklist

- Replace `BlobRestProxy` imports and construction
- Introduce `BlobServiceClient`, then scope down to container and blob clients
- Re-test uploads, downloads, deletes, metadata, and listing
- Re-test all SAS and signed URL flows
- Defer auth modernization until after the client migration is green

## Keep reading

- [Blob overview](../2-storage-blob/0-overview.md)
- [Blob installation](../2-storage-blob/1-installation.md)
- [Blob quickstart](../2-storage-blob/2-quickstart.md)
- [What to Use After microsoft/azure-storage-blob](../9-blog/1-what-to-use-after-microsoft-azure-storage-blob.md)
