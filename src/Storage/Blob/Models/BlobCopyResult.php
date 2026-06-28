<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use Psr\Http\Message\ResponseInterface;

/**
 * Represents Azure Storage blob copy result data.
 */
final class BlobCopyResult
{
    private function __construct(
        public readonly string $copyId,
        public readonly CopyStatus $copyStatus,
        /** The version created for the destination blob when versioning is enabled. */
        public readonly ?string $versionId,
    ) {}

    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(
            $response->getHeaderLine('x-ms-copy-id'),
            CopyStatus::from($response->getHeaderLine('x-ms-copy-status')),
            $response->hasHeader('x-ms-version-id') ? $response->getHeaderLine('x-ms-version-id') : null,
        );
    }
}
