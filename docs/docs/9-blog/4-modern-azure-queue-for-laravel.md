---
sidebar_position: 4
slug: /blog/modern-azure-queue-for-laravel
title: A Modern Azure Queue Driver for Laravel
description: Why Laravel teams should move from squigg/azure-queue-laravel to azure-oss/storage-queue-laravel.
---

Queue migrations are deceptive.

At first glance, moving from `squigg/azure-queue-laravel` to `azure-oss/storage-queue-laravel` looks like a simple config refresh. Rename a few fields, change the driver, move on.

That is partly true. It is also how teams end up underestimating the one thing that matters most in queue code: behavior.

## The package swap matters because operations matter

The old package is tied to the legacy Microsoft Queue SDK.

The new package puts Laravel on top of `azure-oss/storage-queue`, which gives the queue layer a maintained base and a config model that looks more like something you would actually want to support in 2026.

That includes:

- native connection string support
- explicit shared-key config
- SAS-bearing connection strings
- custom endpoints for local and emulator-style setups

None of that is glamorous. All of it reduces friction.

## The real migration question is this

Will your workers behave the same way after the upgrade?

That is the heart of it.

Queue code is rarely judged by how elegant the config file looks. It is judged by whether jobs reappear too early, whether failed work is retried the way you expect, and whether local and cloud environments behave close enough to trust.

That is why the move to `azure-oss/storage-queue-laravel` is worth doing, but also why it should be treated like an application behavior change, not just dependency cleanup.

## What gets better

- config names that read clearly
- queue options like `retry_after`, `time_to_live`, and `after_commit`
- a cleaner path for teams that already store Azure connection strings
- one maintained queue stack from Laravel down to the SDK

That is the sort of foundation you want before your job volume goes up, not after.

## Migrate it like production infrastructure

If you make this switch, test the boring things hard:

- long-running jobs
- retries
- delayed jobs
- local development against custom endpoints or Azurite-style setups

The docs can help with the rename work. Your staging environment should validate the timing work.

## Where to go next

For the exact field mapping and worker-focused checklist, go to [Migrate from squigg/azure-queue-laravel](../8-migration-guides/5-squigg-azure-queue-laravel.md).
