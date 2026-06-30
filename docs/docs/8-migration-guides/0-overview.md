---
sidebar_position: 0
slug: /migration-guides
title: Migration Guides Overview
description: Practical upgrade paths from legacy Azure PHP packages to the community-maintained azure-oss ecosystem.
---

This section exists because Azure PHP search results are full of ghosts.

Old package names still rank well. Old examples still circulate. Old integrations still look plausible until you notice the date, the PHP target, or the dependency chain underneath them.

These guides are here to cut through that.

They are written for teams doing real upgrades, not toy rewrites:

- they compare the old package with the current replacement
- they call out where the migration is genuinely straightforward
- they say plainly when the answer is "not a drop-in replacement yet"

## Start here: which package are you on now?

| Current package | Move to | What kind of migration is it? | Start here |
| --- | --- | --- | --- |
| `microsoft/azure-storage-blob` | `azure-oss/storage-blob` | Direct SDK replacement | [Migrate from microsoft/azure-storage-blob](./1-microsoft-azure-storage-blob.md) |
| `league/flysystem-azure-blob-storage` | `azure-oss/storage-blob-flysystem` | Direct Flysystem replacement | [Migrate from league/flysystem-azure-blob-storage](./2-league-flysystem-azure-blob-storage.md) |
| `matthewbdaly/laravel-azure-storage` | `azure-oss/storage-blob-laravel` | Direct Laravel filesystem replacement | [Migrate from matthewbdaly/laravel-azure-storage](./3-matthewbdaly-laravel-azure-storage.md) |
| `microsoft/azure-storage-queue` | `azure-oss/storage-queue` | Direct SDK replacement | [Migrate from microsoft/azure-storage-queue](./4-microsoft-azure-storage-queue.md) |
| `squigg/azure-queue-laravel` | `azure-oss/storage-queue-laravel` | Direct Laravel queue replacement | [Migrate from squigg/azure-queue-laravel](./5-squigg-azure-queue-laravel.md) |
| `microsoft/azure-storage-file` | `azure-oss/storage-file-share` | Partial replacement today | [Migrate from microsoft/azure-storage-file](./6-microsoft-azure-storage-file.md) |

## What changes across almost every migration

### 1. The PHP baseline jumps forward

The old Microsoft storage SDKs were designed for a very different PHP era.

The `azure-oss` packages target modern PHP and modern framework versions:

- `azure-oss/storage-blob`: PHP `^8.2`
- `azure-oss/storage-queue`: PHP `^8.2`
- `azure-oss/storage-file-share`: PHP `^8.2`
- `azure-oss/storage-blob-flysystem`: PHP `^8.2`
- `azure-oss/storage-blob-laravel`: Laravel 10, 11, 12, and 13
- `azure-oss/storage-queue-laravel`: Laravel 10, 11, 12, and 13

For many teams, that means the dependency upgrade is part of a broader modernization pass. That is normal.

### 2. The client model gets more explicit

The legacy Microsoft SDKs were built around large proxy classes:

- `BlobRestProxy`
- `QueueRestProxy`
- `FileRestProxy`

The newer packages use smaller, better-scoped clients:

- Blob: `BlobServiceClient` -> `BlobContainerClient` -> `BlobClient`
- Queue: `QueueServiceClient` -> `QueueClient`
- File Share: `ShareServiceClient` -> `ShareClient` -> `ShareDirectoryClient` / `ShareFileClient`

That is not just an API rename. It changes how you structure storage code.

### 3. Auth gets less boxed in

The Blob side of the ecosystem especially benefits from the move:

- shared key still works
- connection strings still work
- SAS flows are first-class
- Microsoft Entra ID is available through `azure-oss/identity`

The Laravel Blob package also supports `client_secret`, `client_certificate`, `workload_identity`, and `managed_identity`.

Queue Laravel already covers the auth shapes most apps need in practice, especially connection strings, shared key config, SAS-bearing connection strings, and custom endpoints for local development.

### 4. The ecosystem finally lines up

One of the nicest things about the `azure-oss` packages is that they belong to the same story:

- Blob SDK and Flysystem adapter fit together
- Laravel filesystem builds on the Flysystem layer
- Laravel queue builds on the Queue SDK
- shared HTTP, auth, and SAS behavior lives in `azure-oss/storage-common`

That coherence is worth a lot once your app grows past a single use case.

## The one migration that needs extra honesty

`azure-oss/storage-file-share` should not be sold as a universal drop-in replacement for every `microsoft/azure-storage-file` workload yet.

Today it is strongest for Azure Files service access patterns and SAS generation. If your application mainly wants mounted filesystem behavior, an SMB or NFS mount may still be the better destination.

That is not a weakness in the docs. It is the docs doing their job.

## How to use these guides well

- Pick the guide that matches the package you have today, not the package you eventually want.
- Keep the first migration conservative.
- Re-test auth and signed URL behavior separately from the basic CRUD path.
- Treat queue timing and visibility settings as behavioral changes, not just config changes.

## Choose your path

- Blob SDK: [Migrate from microsoft/azure-storage-blob](./1-microsoft-azure-storage-blob.md)
- Flysystem adapter: [Migrate from league/flysystem-azure-blob-storage](./2-league-flysystem-azure-blob-storage.md)
- Laravel filesystem: [Migrate from matthewbdaly/laravel-azure-storage](./3-matthewbdaly-laravel-azure-storage.md)
- Queue SDK: [Migrate from microsoft/azure-storage-queue](./4-microsoft-azure-storage-queue.md)
- Laravel queue: [Migrate from squigg/azure-queue-laravel](./5-squigg-azure-queue-laravel.md)
- File Share SDK: [Migrate from microsoft/azure-storage-file](./6-microsoft-azure-storage-file.md)

If you want the higher-level, opinionated take first, start with the pages in [Blog](../9-blog/1-what-to-use-after-microsoft-azure-storage-blob.md).
