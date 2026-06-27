<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * @internal
 */
enum RequestConditionSet
{
    case ALL;
    case HTTP_ONLY;
    case DATES_AND_LEASE;
    case MODIFIED_SINCE_AND_LEASE;
    case LEASE_ONLY;
    case DATES_ONLY;

    /**
     * @return list<string>
     */
    public function properties(): array
    {
        return match ($this) {
            self::ALL => ['ifMatch', 'ifModifiedSince', 'ifNoneMatch', 'ifUnmodifiedSince', 'leaseId'],
            self::HTTP_ONLY => ['ifMatch', 'ifModifiedSince', 'ifNoneMatch', 'ifUnmodifiedSince'],
            self::DATES_AND_LEASE => ['ifModifiedSince', 'ifUnmodifiedSince', 'leaseId'],
            self::MODIFIED_SINCE_AND_LEASE => ['ifModifiedSince', 'leaseId'],
            self::LEASE_ONLY => ['leaseId'],
            self::DATES_ONLY => ['ifModifiedSince', 'ifUnmodifiedSince'],
        };
    }
}
