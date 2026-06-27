<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use AzureOss\Storage\Common\Models\ETag;

/**
 * Represents Azure Storage blob request conditions data.
 */
final class BlobRequestConditions
{
    public function __construct(
        public ?ETag $ifMatch = null,
        public ?\DateTimeInterface $ifModifiedSince = null,
        public ?ETag $ifNoneMatch = null,
        public ?\DateTimeInterface $ifUnmodifiedSince = null,
        public ?string $leaseId = null,
    ) {}

    /**
     * @internal
     *
     * @return array<string, string>
     */
    public function toHeaders(
        string $operation,
        RequestConditionSet $set,
        string $prefix = '',
    ): array {
        $values = [
            'ifMatch' => $this->ifMatch,
            'ifModifiedSince' => $this->ifModifiedSince,
            'ifNoneMatch' => $this->ifNoneMatch,
            'ifUnmodifiedSince' => $this->ifUnmodifiedSince,
            'leaseId' => $this->leaseId,
        ];
        $supported = array_flip($set->properties());
        $unsupported = array_keys(array_filter(
            $values,
            fn (mixed $value, string $property): bool => $value !== null && ! isset($supported[$property]),
            ARRAY_FILTER_USE_BOTH,
        ));

        if ($unsupported !== []) {
            throw new \InvalidArgumentException(sprintf(
                '%s does not support request condition(s): %s.',
                $operation,
                implode(', ', $unsupported),
            ));
        }

        $headerNames = $prefix === '' ? [
            'ifMatch' => 'If-Match',
            'ifModifiedSince' => 'If-Modified-Since',
            'ifNoneMatch' => 'If-None-Match',
            'ifUnmodifiedSince' => 'If-Unmodified-Since',
            'leaseId' => 'x-ms-lease-id',
        ] : [
            'ifMatch' => $prefix.'if-match',
            'ifModifiedSince' => $prefix.'if-modified-since',
            'ifNoneMatch' => $prefix.'if-none-match',
            'ifUnmodifiedSince' => $prefix.'if-unmodified-since',
            'leaseId' => $prefix.'lease-id',
        ];

        return array_filter([
            $headerNames['ifMatch'] => isset($supported['ifMatch']) && $this->ifMatch !== null ? (string) $this->ifMatch : null,
            $headerNames['ifModifiedSince'] => isset($supported['ifModifiedSince']) && $this->ifModifiedSince !== null ? self::formatDate($this->ifModifiedSince) : null,
            $headerNames['ifNoneMatch'] => isset($supported['ifNoneMatch']) && $this->ifNoneMatch !== null ? (string) $this->ifNoneMatch : null,
            $headerNames['ifUnmodifiedSince'] => isset($supported['ifUnmodifiedSince']) && $this->ifUnmodifiedSince !== null ? self::formatDate($this->ifUnmodifiedSince) : null,
            $headerNames['leaseId'] => isset($supported['leaseId']) ? $this->leaseId : null,
        ], fn (?string $value): bool => $value !== null);
    }

    private static function formatDate(\DateTimeInterface $date): string
    {
        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('GMT'))
            ->format('D, d M Y H:i:s \G\M\T');
    }
}
