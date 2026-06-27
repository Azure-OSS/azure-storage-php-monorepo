<?php

declare(strict_types=1);

namespace AzureOss\Storage\Tests\Blob\Unit;

use AzureOss\Storage\Blob\Models\BlobContainerProperties;
use AzureOss\Storage\Blob\Models\BlobProperties;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PropertiesETagTest extends TestCase
{
    #[Test]
    public function blob_properties_read_etag_from_response_headers(): void
    {
        $properties = BlobProperties::fromResponseHeaders(new Response(200, [
            'Last-Modified' => 'Wed, 01 Jan 2025 12:34:56 GMT',
            'Content-Length' => '123',
            'Content-Type' => 'text/plain',
            'ETag' => '"blob-etag"',
        ]));

        self::assertSame('"blob-etag"', (string) $properties->eTag);
    }

    #[Test]
    public function blob_properties_read_etag_from_xml(): void
    {
        $properties = BlobProperties::fromXml(new \SimpleXMLElement(<<<'XML'
<Properties>
    <Last-Modified>Wed, 01 Jan 2025 12:34:56 GMT</Last-Modified>
    <Etag>"blob-list-etag"</Etag>
    <Content-Length>123</Content-Length>
    <Content-Type>text/plain</Content-Type>
</Properties>
XML));

        self::assertSame('"blob-list-etag"', (string) $properties->eTag);
    }

    #[Test]
    public function container_properties_read_etag_from_response_headers(): void
    {
        $properties = BlobContainerProperties::fromResponseHeaders(new Response(200, [
            'Last-Modified' => 'Wed, 01 Jan 2025 12:34:56 GMT',
            'ETag' => '"container-etag"',
        ]));

        self::assertSame('"container-etag"', (string) $properties->eTag);
    }

    #[Test]
    public function container_properties_read_etag_from_xml(): void
    {
        $properties = BlobContainerProperties::fromXml(new \SimpleXMLElement(<<<'XML'
<Properties>
    <Last-Modified>Wed, 01 Jan 2025 12:34:56 GMT</Last-Modified>
    <Etag>"container-list-etag"</Etag>
</Properties>
XML));

        self::assertSame('"container-list-etag"', (string) $properties->eTag);
    }
}
