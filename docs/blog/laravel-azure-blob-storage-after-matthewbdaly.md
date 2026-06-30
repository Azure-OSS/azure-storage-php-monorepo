---
slug: laravel-azure-blob-storage-after-matthewbdaly-laravel-azure-storage
title: The Modern Laravel Blob Stack After matthewbdaly/laravel-azure-storage
description: What Laravel teams should use for Azure Blob Storage now, and why the upgrade is bigger than a disk config rename.
---

If your Laravel app still uses `matthewbdaly/laravel-azure-storage`, the replacement package is `azure-oss/storage-blob-laravel`.

That is the practical answer.

The more interesting answer is why the move feels bigger than a disk-driver swap.

## Your old disk is sitting on an old stack

The package may look like a thin Laravel integration, but underneath it sits a chain of legacy dependencies:

- the old Laravel driver
- the old League Flysystem Azure adapter
- the old Microsoft Blob SDK

So when teams say, "The filesystem config still works, why touch it?", they are only looking at the top layer.

`azure-oss/storage-blob-laravel` replaces that entire stack with a maintained Blob SDK, a maintained Flysystem adapter, and a Laravel driver designed to fit today’s framework expectations.

## This is where the upgrade gets interesting

Laravel teams usually care about three things more than package history.

### 1. Cleaner config

The new driver is easier to read at a glance.

Instead of old field names and compatibility baggage, you get config that spells out its intent:

- `connection_string`
- `account_name`
- `account_key`
- `temporary_url`
- `is_public_container`

That sounds cosmetic until you have to audit production config across four environments and three teammates.

### 2. A real modern auth story

This is the part that changes architecture conversations.

The new package supports:

- `client_secret`
- `client_certificate`
- `workload_identity`
- `managed_identity`

If your Laravel app runs on Azure, that means you are no longer trapped in the world where long-lived storage secrets feel like the only practical option.

### 3. URL behavior you can reason about

Blob disks always get judged on URLs.

Not in demos, but in the awkward cases:

- public container links
- signed temporary URLs
- custom origins
- Azure Front Door in front of storage

This package gives those concerns a cleaner home instead of leaving them as half-documented quirks.

## The best rollout is usually the boring one

Start with a connection string. Keep the disk name stable. Prove uploads, downloads, `Storage::url()`, and `Storage::temporaryUrl()` still behave the way your app expects.

Then, once the migration is quiet in production, decide whether you want to modernize authentication.

That pacing matters. A good migration is not the one with the biggest ambition. It is the one your team can safely ship.

## Where to go next

For the exact config mapping and rollout checklist, read [Migrate from matthewbdaly/laravel-azure-storage](/storage-blob-laravel/migrate-from-matthewbdaly-laravel-azure-storage).
