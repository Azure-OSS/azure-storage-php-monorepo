<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Blob\Unit;

use AzureOss\Storage\Blob\Models\BlobSnapshotInfo;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BlobSnapshotInfoTest extends TestCase
{
    #[Test]
    public function it_deserializes_from_response_headers(): void
    {
        $info = BlobSnapshotInfo::fromResponse(new Response(201, [
            'x-ms-snapshot' => '2026-06-28T10:20:30.1234567Z',
            'ETag' => '"snapshot-etag"',
            'Last-Modified' => 'Sun, 28 Jun 2026 10:20:30 GMT',
            'x-ms-version-id' => '2026-06-28T10:20:30.1234567Z',
            'x-ms-request-server-encrypted' => 'true',
        ]));

        self::assertSame('2026-06-28T10:20:30.1234567Z', $info->snapshot);
        self::assertSame('"snapshot-etag"', (string) $info->eTag);
        self::assertSame('2026-06-28T10:20:30+00:00', $info->lastModified->format(\DateTimeInterface::ATOM));
        self::assertSame('2026-06-28T10:20:30.1234567Z', $info->versionId);
        self::assertTrue($info->isServerEncrypted);
    }
}
