---
sidebar_position: 1
slug: /blog/what-to-use-after-microsoft-azure-storage-blob
title: What to Use After microsoft/azure-storage-blob
description: The practical replacement for the retired Microsoft Azure Blob SDK in modern PHP projects.
---

Search for Azure Blob Storage in PHP and you still fall into a small time warp.

The old `microsoft/azure-storage-blob` package still has years of examples, Stack Overflow answers, and copy-pasted snippets working in its favor. But if the real question is, "What should we build on today?", the answer is much cleaner:

Use `azure-oss/storage-blob`.

Not because it is newer for the sake of being newer. Because it is the package that actually matches how modern PHP teams work now: current PHP, clearer clients, current docs, and a path into the rest of the `azure-oss` ecosystem if your app grows beyond a few direct Blob calls.

## The old package usually breaks trust in slow motion

Teams rarely wake up and schedule "The Great Blob Migration."

What usually happens is quieter:

- a PHP upgrade is overdue
- a new feature needs signed URLs or tags
- a teammate wants Laravel or Flysystem integration
- someone tries to standardize auth across Azure services

That is when the age of the old package starts to matter. It is not one dramatic failure. It is friction everywhere around the edges.

## Why `azure-oss/storage-blob` is the right replacement

It gives you a better center of gravity for the codebase:

- `BlobServiceClient` for account-level work
- `BlobContainerClient` for container-scoped work
- `BlobClient` for the blob you actually care about

That client model sounds small, but it changes the feel of the code. Instead of passing container and blob names through every method call, your code starts to model the storage structure directly.

## The short comparison

| Question | Legacy package | Modern package |
| --- | --- | --- |
| What do I install? | `microsoft/azure-storage-blob` | `azure-oss/storage-blob` |
| What does the API revolve around? | `BlobRestProxy` | Service, container, and blob clients |
| Can I keep using a connection string first? | Yes | Yes |
| Can I modernize auth later? | Limited story | Yes, through `azure-oss/identity` |
| Does it line up with Flysystem and Laravel? | Not really | Yes |

## The best part of this migration is not glamorous

The payoff is not a flashy demo.

It is that six months later you have one Blob stack instead of a museum exhibit:

- direct SDK usage on a maintained package
- Flysystem support on the same foundation
- Laravel integrations that are not built on a deprecated core
- docs that describe the package you are actually using

That is the kind of migration that keeps paying rent.

## Who should move first

You are a strong candidate for an easy first pass if:

- your app already runs on PHP 8.2+
- you still authenticate with a connection string
- your Blob usage is mostly uploads, downloads, deletes, listing, and signed URLs
- you want to keep the first migration boring and safe

That last point matters. You do not need to adopt Microsoft Entra ID, redesign your filesystem abstraction, and rewrite every storage call in one sprint. You can move the package first and modernize the rest in phases.

## Where to go next

If you want the actual code-and-config path, read [Migrate from microsoft/azure-storage-blob](../8-migration-guides/1-microsoft-azure-storage-blob.md).
