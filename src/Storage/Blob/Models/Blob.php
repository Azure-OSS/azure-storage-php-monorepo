<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Models;

/**
 * Represents Azure Storage blob data.
 */
final class Blob
{
    private function __construct(
        public readonly string $name,
        public readonly BlobProperties $properties,
    ) {}

    public static function fromXml(\SimpleXMLElement $xml): self
    {
        return new self(
            (string) $xml->Name,
            BlobProperties::fromXml($xml->Properties),
        );
    }
}
