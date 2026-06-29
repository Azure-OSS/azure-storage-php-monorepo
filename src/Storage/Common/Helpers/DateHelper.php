<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Helpers;

/**
 * Shared date formatting helpers used across Storage packages.
 *
 * @internal
 */
final class DateHelper
{
    /**
     * Formats the given date as an RFC 3339 / ISO 8601 UTC timestamp without fractional seconds.
     */
    public static function formatAs8601Zulu(\DateTimeInterface $date): string
    {
        return \DateTime::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');
    }
}
