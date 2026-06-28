<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Configures creation of a read-only blob snapshot.
 */
final class CreateSnapshotOptions
{
    /**
     * @param  array<string, string>  $metadata  Metadata for the snapshot. When empty, Azure copies the base blob metadata.
     * @param  BlobRequestConditions|null  $conditions  Conditions that the base blob must satisfy before the snapshot is created.
     */
    public function __construct(
        public array $metadata = [],
        public ?BlobRequestConditions $conditions = null,
    ) {}
}
