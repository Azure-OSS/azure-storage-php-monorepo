---
sidebar_position: 2
title: Quickstart
---

## Prerequisites

- `config/filesystems.php` contains an `azure` disk using `driver => azure-storage-blob`

Example `.env` values:

```bash
AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=...;AccountName=...;AccountKey=...;EndpointSuffix=core.windows.net"
AZURE_STORAGE_CONTAINER="quickstart"
```

## Basic Usage

```php
use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('azure');

// Write
$disk->put('docs/hello.txt', 'Hello from Laravel');

// Read
$content = $disk->get('docs/hello.txt');

// Exists
$exists = $disk->exists('docs/hello.txt');

// Copy / move
$disk->copy('docs/hello.txt', 'docs/hello-copy.txt');
$disk->move('docs/hello-copy.txt', 'docs/hello-moved.txt');

// List
$files = $disk->allFiles('docs');

// Delete
$disk->delete('docs/hello-moved.txt');
```

## URLs

```php
use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('azure');

$url = $disk->url('images/logo.png'); // SAS URL by default
```

If your container is public, set `is_public_container => true` in disk config to return direct blob URLs without SAS query parameters.

Temporary URL (SAS):

```php
$temporaryUrl = Storage::disk('azure')->temporaryUrl(
    'exports/report.csv',
    now()->addMinutes(15)
);
```

You can also override the response headers returned by the temporary URL:

```php
$temporaryUrl = Storage::disk('azure')->temporaryUrl(
    'exports/report.csv',
    now()->addMinutes(15),
    [
        'httpHeaders' => [
            'contentDisposition' => 'attachment; filename="report.csv"',
            'contentType' => 'text/csv',
        ],
    ]
);
```

Supported `httpHeaders` keys for temporary URLs:

- `cacheControl`
- `contentDisposition`
- `contentEncoding`
- `contentLanguage`
- `contentType`

Temporary upload URL:

```php
$upload = Storage::disk('azure')->temporaryUploadUrl(
    'uploads/photo.jpg',
    now()->addMinutes(10),
    ['content-type' => 'image/jpeg']
);

// $upload['url'] contains the signed URL
// $upload['headers'] contains required request headers
```

## Upload Options (HTTP Headers)

You can pass Flysystem write options as the third argument to `put()`:

```php
$css = 'body { color: #0f172a; }';

Storage::disk('azure')->put('assets/app.css', $css, [
    'httpHeaders' => [
        'cacheControl' => 'public, max-age=31536000',
        'contentType' => 'text/css',
    ],
]);
```

## Conditional Writes

Pass conditions to `put()`. For example, the wildcard ETag can ensure that a write only succeeds when the file does not already exist:

```php
use Illuminate\Support\Facades\Storage;

Storage::disk('azure')->put('documents/report.json', $contents, [
    'conditions' => [
        'ifNoneMatch' => '*',
    ],
]);
```

Supported condition keys are `ifMatch`, `ifNoneMatch`, and `leaseId`.

For more advanced optimistic-concurrency scenarios, use the lower-level Blob client to retrieve an existing ETag and pass its string value as `ifMatch`. Lease IDs and lease lifecycle operations are also available through the lower-level Blob and lease clients. Set `throw` to `true` in the disk configuration when the application needs to handle failed conditions or lease mismatches as exceptions.

## Notes

- When using Microsoft Entra ID credentials, the disk cannot generate SAS URLs (`providesTemporaryUrls()` returns `false`).
- Optional `prefix` (or `root`) in disk config scopes all paths to a subfolder-like prefix in the container.
