<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use AzureOss\Storage\Blob\Helpers\DateHelper;
use AzureOss\Storage\Common\Models\ETag;
use Psr\Http\Message\ResponseInterface;

/**
 * Describes a newly created blob snapshot.
 */
final class BlobSnapshotInfo
{
    private function __construct(
        /** The opaque snapshot identifier used to address this snapshot. */
        public readonly string $snapshot,
        /** The entity tag assigned to the snapshot. */
        public readonly ETag $eTag,
        /** The time at which the snapshot was created. */
        public readonly \DateTimeInterface $lastModified,
        /** The version created by the operation when blob versioning is enabled. */
        public readonly ?string $versionId,
        /** Whether the snapshot metadata was encrypted by the service. */
        public readonly bool $isServerEncrypted,
    ) {}

    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(
            $response->getHeaderLine('x-ms-snapshot'),
            new ETag($response->getHeaderLine('ETag')),
            DateHelper::deserializeDateRfc1123Date($response->getHeaderLine('Last-Modified')),
            $response->hasHeader('x-ms-version-id') ? $response->getHeaderLine('x-ms-version-id') : null,
            filter_var($response->getHeaderLine('x-ms-request-server-encrypted'), FILTER_VALIDATE_BOOL),
        );
    }
}
