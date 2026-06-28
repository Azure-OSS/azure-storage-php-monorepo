<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Common\Unit;

use AzureOss\Storage\Common\Models\ETag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ETagTest extends TestCase
{
    #[Test]
    public function etag_can_represent_all_resources(): void
    {
        self::assertSame('*', (string) ETag::all());
    }

    #[Test]
    public function etag_preserves_azure_header_value(): void
    {
        $eTag = new ETag('"0x8DBCAFB82EAFB84"');

        self::assertSame('"0x8DBCAFB82EAFB84"', (string) $eTag);
        self::assertTrue($eTag->equals('"0x8DBCAFB82EAFB84"'));
    }
}
