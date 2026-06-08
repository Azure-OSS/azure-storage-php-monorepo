<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

use AzureOss\Storage\Blob\Exceptions\DeserializationException;
use AzureOss\Storage\Blob\Helpers\DeprecationHelper;
use AzureOss\Storage\Blob\Helpers\MetadataHelper;
use AzureOss\Storage\Common\Models\ETag;
use Psr\Http\Message\ResponseInterface;

final class BlobContainerProperties
{
    /**
     * @deprecated will be private in version 2
     *
     * @param  array<string>  $metadata
     */
    public function __construct(
        public readonly \DateTimeInterface $lastModified,
        public readonly array $metadata,
        public readonly ?ETag $eTag = null,
    ) {
        DeprecationHelper::constructorWillBePrivate(self::class, '2.0');
    }

    public static function fromResponseHeaders(ResponseInterface $response): self
    {
        $lastModified = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC1123, $response->getHeaderLine('Last-Modified'));
        if ($lastModified === false) {
            throw new DeserializationException('Azure returned a malformed date.');
        }

        /** @phpstan-ignore-next-line */
        return new self(
            $lastModified,
            MetadataHelper::headersToMetadata($response->getHeaders()),
            $response->hasHeader('ETag') ? new ETag($response->getHeaderLine('ETag')) : null,
        );
    }

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        $lastModified = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC1123, (string) $xml->{'Last-Modified'});
        if ($lastModified === false) {
            throw new DeserializationException('Azure returned a malformed date.');
        }

        $eTag = (string) $xml->Etag !== '' ? (string) $xml->Etag : (string) $xml->ETag;

        /** @phpstan-ignore-next-line */
        return new self(
            $lastModified,
            [],
            $eTag !== '' ? new ETag($eTag) : null,
        );
    }
}
