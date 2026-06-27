<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use AzureOss\Storage\Blob\Helpers\DateHelper;
use AzureOss\Storage\Common\Models\ETag;
use Psr\Http\Message\ResponseInterface;

/**
 * Represents Azure Storage released object info data.
 */
final class ReleasedObjectInfo
{
    public function __construct(
        public readonly ETag $eTag,
        public readonly \DateTimeInterface $lastModified,
    ) {}

    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(
            new ETag($response->getHeaderLine('ETag')),
            DateHelper::deserializeDateRfc1123Date($response->getHeaderLine('Last-Modified')),
        );
    }
}
