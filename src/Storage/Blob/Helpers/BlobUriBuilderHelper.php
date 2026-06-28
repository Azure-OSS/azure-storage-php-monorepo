<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Helpers;

use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\UriInterface;

/**
 * Builds blob URIs that target a snapshot or version while preserving existing query parameters.
 *
 * @internal
 */
final class BlobUriBuilderHelper
{
    public static function withSnapshot(UriInterface $uri, ?string $snapshot): UriInterface
    {
        return self::withSelector($uri, 'snapshot', $snapshot, 'versionid');
    }

    public static function withVersion(UriInterface $uri, ?string $versionId): UriInterface
    {
        return self::withSelector($uri, 'versionid', $versionId, 'snapshot');
    }

    private static function withSelector(UriInterface $uri, string $name, ?string $value, string $otherName): UriInterface
    {
        $query = Query::parse($uri->getQuery());

        unset($query[$name]);

        if ($value !== null && $value !== '') {
            unset($query[$otherName]);
            $query[$name] = $value;
        }

        return $uri->withQuery(Query::build($query));
    }
}
