---
slug: migrating-from-league-flysystem-azure-blob-storage
title: Leaving league/flysystem-azure-blob-storage Behind
description: Why this Flysystem migration is usually straightforward and still worth doing.
---

`league/flysystem-azure-blob-storage` is one of those packages that still gets the benefit of a great name.

It sounds official. It sounds familiar. It sounds like the obvious thing to install.

The problem is that it is abandoned, and more importantly, it keeps your Flysystem layer tied to the legacy Microsoft Blob SDK.

That is why the modern move is not just "replace one adapter with another." It is really about getting your storage abstraction off old foundations and onto `azure-oss/storage-blob-flysystem`.

## This is the least dramatic migration in the set

That is good news.

If you already use Flysystem v3, this change is usually more like swapping the engine than rebuilding the car:

- the Flysystem mental model stays the same
- your app still talks to a `Filesystem`
- the big change is how the Azure adapter is created

So while the package name change matters, the real upgrade happens one layer down.

## What improves under the hood

The old adapter is built on `microsoft/azure-storage-blob`.

The new one is built on `azure-oss/storage-blob`, which means the adapter now inherits a storage stack that is actively aligned with the rest of this ecosystem:

- current Blob clients
- modern SAS support
- better docs for public URLs and temporary URLs
- easier handoff between direct SDK code and Flysystem-backed code

That matters any time your project stops being "just a filesystem disk" and starts needing storage-specific behavior.

## The one code change to understand

The old adapter starts with a `BlobRestProxy` and a container name.

The new adapter starts with a `BlobContainerClient`.

That is a healthier boundary. It means the adapter receives exactly the scope it needs, no more and no less. Once you see that, the migration becomes much easier to reason about.

## When this migration is almost boring

That is the sweet spot:

- you already construct the adapter in one place
- you already use Flysystem v3
- your app does not depend on odd adapter-specific behavior
- your biggest concern is URL generation and upload behavior

In that scenario, this is often a tidy refactor with a good payoff.

## Why it is still worth doing

Because abandoned infrastructure code has a way of becoming "fine" right up until the moment it is not.

If your Blob adapter is the bridge between application code and storage, it is a good place to remove uncertainty. Moving to `azure-oss/storage-blob-flysystem` gives you a maintained adapter on top of a maintained SDK, and that is a much better place to keep building from.

## Where to go next

For the concrete namespace, constructor, and URL behavior changes, start with [Migrate from league/flysystem-azure-blob-storage](/storage-blob-flysystem/migrate-from-league-flysystem-azure-blob-storage).
