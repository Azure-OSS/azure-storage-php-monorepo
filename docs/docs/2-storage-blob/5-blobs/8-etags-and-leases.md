---
sidebar_position: 8
title: ETags and leases
---

ETags provide optimistic concurrency: an operation succeeds only when the blob is still at the expected version. Leases provide exclusive write access for a fixed or infinite period.

Both are passed to blob operations through `BlobRequestConditions`.

## Get A Blob ETag

Blob properties contain the current ETag:

```php
<?php

use AzureOss\Storage\Blob\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('my-container')->getBlobClient('document.json');

$properties = $blob->getProperties();
$eTag = $properties->eTag;

if ($eTag === null) {
    throw new \RuntimeException('Azure did not return an ETag.');
}
```

Keep the `ETag` object intact. Its string value includes the quoting required by Azure.

## Overwrite Only The Expected Version

Pass the ETag as `ifMatch` to prevent overwriting a newer version of the blob:

```php
use AzureOss\Storage\Blob\Models\BlobRequestConditions;
use AzureOss\Storage\Blob\Models\UploadBlobOptions;

$blob->upload(
    json_encode(['status' => 'approved'], JSON_THROW_ON_ERROR),
    new UploadBlobOptions(
        conditions: new BlobRequestConditions(ifMatch: $eTag),
    ),
);
```

If another writer changed the blob after the ETag was retrieved, Azure rejects the upload with `ConditionNotMet`:

```php
use AzureOss\Storage\Blob\Exceptions\BlobStorageException;
use AzureOss\Storage\Blob\Models\BlobErrorCode;

try {
    $blob->upload($content, new UploadBlobOptions(
        conditions: new BlobRequestConditions(ifMatch: $eTag),
    ));
} catch (BlobStorageException $exception) {
    if ($exception->errorCode !== BlobErrorCode::ConditionNotMet) {
        throw $exception;
    }

    // Reload the current version and resolve the conflict.
}
```

## Create A Blob Only If It Does Not Exist

Use the wildcard ETag as `ifNoneMatch` for an atomic create-only upload:

```php
use AzureOss\Storage\Common\Models\ETag;

$blob->upload(
    $content,
    new UploadBlobOptions(
        conditions: new BlobRequestConditions(
            ifNoneMatch: ETag::all(),
        ),
    ),
);
```

## Other Conditional Operations

`BlobRequestConditions` can contain `ifMatch`, `ifNoneMatch`, `ifModifiedSince`, `ifUnmodifiedSince`, and `leaseId`. The conditions are accepted by the option object for each operation:

| Operation | Options |
| --- | --- |
| `upload()` | `UploadBlobOptions` |
| `downloadStreaming()` | `DownloadBlobOptions` |
| `getProperties()` | `GetBlobPropertiesOptions` |
| `delete()` / `deleteIfExists()` | `DeleteBlobOptions` |
| `setMetadata()` | `SetBlobMetadataOptions` |
| `setHttpHeaders()` | `SetBlobHttpHeadersOptions` |
| `getTags()` / `setTags()` | `GetBlobTagsOptions` / `SetBlobTagsOptions` |
| `startCopyFromUri()` / `syncCopyFromUri()` | `StartCopyFromUriOptions` / `SyncCopyFromUriOptions` |

Azure does not allow every condition on every operation. The SDK rejects unsupported combinations with an `InvalidArgumentException` before sending the request.

## Acquire And Use A Blob Lease

A blob must exist before it can be leased. Acquire a lease, then include its ID in write conditions:

```php
$blob->upload('initial content');

$leaseClient = $blob->getBlobLeaseClient();
$lease = $leaseClient->acquire(30);

if ($lease->leaseId === null) {
    throw new \RuntimeException('Azure did not return a lease ID.');
}

try {
    $blob->upload(
        'protected update',
        new UploadBlobOptions(
            conditions: new BlobRequestConditions(
                leaseId: $lease->leaseId,
            ),
        ),
    );
} finally {
    $leaseClient->release();
}
```

While the lease is active, writes that omit the lease ID fail with `LeaseIdMissing`; writes with a different ID fail with a lease mismatch error.

## Renew, Change, Release, Or Break A Lease

The lease client remembers the ID returned by `acquire()` and updates it after `change()`:

```php
$leaseClient = $blob->getBlobLeaseClient();
$leaseClient->acquire(30);

$leaseClient->renew();
$leaseClient->change('11111111-1111-4111-8111-111111111111');
$leaseClient->release();
```

To work with a lease ID saved by another process, provide it when creating the lease client:

```php
$leaseClient = $blob->getBlobLeaseClient($storedLeaseId);
$leaseClient->renew();
```

Breaking a lease does not require its lease ID:

```php
$blob->getBlobLeaseClient()->break(0);
```

Use `BlobLeaseClient::INFINITE_LEASE_DURATION` when acquiring an infinite lease:

```php
use AzureOss\Storage\Blob\Specialized\BlobLeaseClient;

$lease = $blob
    ->getBlobLeaseClient()
    ->acquire(BlobLeaseClient::INFINITE_LEASE_DURATION);
```

## Container Leases

Containers expose the same lease client entry point:

```php
$container = $service->getContainerClient('my-container');
$leaseClient = $container->getBlobLeaseClient();
$lease = $leaseClient->acquire(30);
```

Pass the returned lease ID through the container operation's `BlobRequestConditions`. Container operations support a smaller subset of conditions than blob operations.

