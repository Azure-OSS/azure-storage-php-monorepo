<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\BlobFlysystem\Unit;

use AzureOss\Storage\BlobFlysystem\Support\ConfigArrayParser;
use PHPUnit\Framework\TestCase;

final class ConfigArrayParserTest extends TestCase
{
    public function test_parse_int_returns_null_for_missing_and_null_values(): void
    {
        self::assertNull(ConfigArrayParser::parseIntFromArray([], 'size'));
        self::assertNull(ConfigArrayParser::parseIntFromArray(['size' => null], 'size'));
    }

    public function test_parse_int_rejects_non_integer_values(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('size must be an int.');

        ConfigArrayParser::parseIntFromArray(['size' => '4'], 'size');
    }

    public function test_parse_array_returns_null_for_missing_values_and_rejects_scalars(): void
    {
        self::assertNull(ConfigArrayParser::parseArrayFromArray([], 'headers'));

        try {
            ConfigArrayParser::parseArrayFromArray(['headers' => 'invalid'], 'headers');
            self::fail('Expected parseArrayFromArray to reject scalars.');
        } catch (\RuntimeException $exception) {
            self::assertSame('headers must be an array.', $exception->getMessage());
        }
    }

    public function test_parse_string_returns_null_for_missing_values_and_uses_context_in_errors(): void
    {
        self::assertNull(ConfigArrayParser::parseStringFromArray([], 'contentType'));
        self::assertNull(ConfigArrayParser::parseStringFromArray(['contentType' => null], 'contentType'));
        self::assertSame(
            'text/plain',
            ConfigArrayParser::parseStringFromArray(['contentType' => 'text/plain'], 'contentType'),
        );

        try {
            ConfigArrayParser::parseStringFromArray(
                ['contentType' => 123],
                'contentType',
                'httpHeaders.',
            );
            self::fail('Expected parseStringFromArray to reject non-strings.');
        } catch (\RuntimeException $exception) {
            self::assertSame(
                'httpHeaders.contentType must be a string.',
                $exception->getMessage(),
            );
        }
    }
}
