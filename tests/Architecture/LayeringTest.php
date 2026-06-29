<?php

declare(strict_types=1);

$testNamespaces = ['AzureOss\\Tests'];

arch('storage common does not depend on higher-level storage packages')
    ->expect('AzureOss\\Storage\\Common')
    ->not->toUse([
        'AzureOss\\Storage\\Blob',
        'AzureOss\\Storage\\BlobFlysystem',
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\File\\Share',
        'AzureOss\\Storage\\Queue',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('blob stays isolated from sibling and integration packages')
    ->expect('AzureOss\\Storage\\Blob')
    ->not->toUse([
        'AzureOss\\Storage\\BlobFlysystem',
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\File\\Share',
        'AzureOss\\Storage\\Queue',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('queue stays isolated from sibling and integration packages')
    ->expect('AzureOss\\Storage\\Queue')
    ->not->toUse([
        'AzureOss\\Storage\\Blob',
        'AzureOss\\Storage\\BlobFlysystem',
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\File\\Share',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('file share stays isolated from other storage service packages')
    ->expect('AzureOss\\Storage\\File\\Share')
    ->not->toUse([
        'AzureOss\\Storage\\Blob',
        'AzureOss\\Storage\\BlobFlysystem',
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\Queue',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('blob flysystem does not depend on higher-level integrations')
    ->expect('AzureOss\\Storage\\BlobFlysystem')
    ->not->toUse([
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\File\\Share',
        'AzureOss\\Storage\\Queue',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('blob flysystem bundle stays isolated from unrelated packages')
    ->expect('AzureOss\\Storage\\BlobFlysystemBundle')
    ->not->toUse([
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\File\\Share',
        'AzureOss\\Storage\\Queue',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('blob laravel stays isolated from unrelated packages')
    ->expect('AzureOss\\Storage\\BlobLaravel')
    ->not->toUse([
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\File\\Share',
        'AzureOss\\Storage\\Queue',
        'AzureOss\\Storage\\QueueLaravel',
    ])
    ->ignoring($testNamespaces);

arch('queue laravel stays isolated from unrelated packages')
    ->expect('AzureOss\\Storage\\QueueLaravel')
    ->not->toUse([
        'AzureOss\\Storage\\Blob',
        'AzureOss\\Storage\\BlobFlysystem',
        'AzureOss\\Storage\\BlobFlysystemBundle',
        'AzureOss\\Storage\\BlobLaravel',
        'AzureOss\\Storage\\File\\Share',
    ])
    ->ignoring($testNamespaces);
