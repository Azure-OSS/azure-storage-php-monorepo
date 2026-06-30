<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Queue\Requests\QueueMessageRequestBody;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueueMessageRequestBodyTest extends TestCase
{
    #[Test]
    public function it_serializes_message_text_to_xml(): void
    {
        $body = new QueueMessageRequestBody('hello world');

        $xml = $body->toXml()->asXML();

        self::assertIsString($xml);
        self::assertStringContainsString('<QueueMessage>', $xml);
        self::assertStringContainsString('<MessageText>hello world</MessageText>', $xml);
    }
}
