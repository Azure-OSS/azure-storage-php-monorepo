---
slug: azure-file-share-after-microsoft-azure-storage-file
title: What to Use After microsoft/azure-storage-file
description: How to choose between azure-oss/storage-file-share and a mounted Azure File Share.
---

The honest answer to "What should I use after `microsoft/azure-storage-file`?" is not a tidy one-liner.

Sometimes the right answer is `azure-oss/storage-file-share`.

Sometimes the right answer is not a PHP SDK at all.

And that honesty is exactly what good migration advice should sound like here.

## Blob and Queue are replacements. File Share is a choice.

With Blob and Queue, the story is fairly direct: old package out, new package in.

Azure Files is different because a lot of applications do not actually want a service SDK. They want a normal filesystem they can mount, read, write, and move through familiar I/O operations.

That means the first migration question is not "Which package replaces this package?"

It is "What problem is the app really solving?"

## Choose `azure-oss/storage-file-share` when you need Azure Files semantics

The package is a strong fit when your app needs Azure-aware service behavior, especially around:

- share access
- directory and file client navigation
- SAS generation
- Azure Files-specific access patterns

If that is your use case, the package gives you a modern baseline and a much healthier long-term direction than staying on the retired Microsoft SDK.

## Choose a mounted share when you really want a filesystem

If your application mostly wants to:

- open and write files like local storage
- move paths around
- let existing file APIs do the work
- treat Azure Files as infrastructure rather than business logic

then an SMB or NFS mount is often the more natural design.

That may feel less exciting than a library migration. It is often more correct.

## Why this kind of guidance matters

Teams remember when documentation helps them avoid the wrong abstraction.

If you oversell File Share as a drop-in SDK replacement for every legacy workload, people will discover the gaps the hard way. If you help them choose the right path up front, they are far more likely to trust the rest of the ecosystem.

That trust is worth more than a smoother headline.

## Where to go next

If you are evaluating the SDK path seriously, start with [Migrate from microsoft/azure-storage-file](/storage-file-share/migrate-from-microsoft-azure-storage-file).
