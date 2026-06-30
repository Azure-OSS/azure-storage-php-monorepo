<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Queue\Exceptions\DeserializationException;
use AzureOss\Storage\Queue\Responses\SendMessageResponseBody;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SendMessageResponseBodyTest extends TestCase
{
    #[Test]
    public function it_deserializes_a_receipt_from_a_response(): void
    {
        $receipt = SendMessageResponseBody::fromResponse(new Response(201, body: <<<'XML'
            <QueueMessagesList>
                <QueueMessage>
                    <MessageId>message-id</MessageId>
                    <InsertionTime>Sun, 27 Sep 2009 18:41:57 GMT</InsertionTime>
                    <ExpirationTime>Sun, 04 Oct 2009 18:41:57 GMT</ExpirationTime>
                    <PopReceipt>pop-receipt</PopReceipt>
                    <TimeNextVisible>Sun, 27 Sep 2009 18:42:27 GMT</TimeNextVisible>
                </QueueMessage>
            </QueueMessagesList>
            XML));

        self::assertSame('message-id', $receipt->messageId);
        self::assertSame('pop-receipt', $receipt->popReceipt);
    }

    #[Test]
    public function it_rejects_malformed_xml_without_a_queue_message(): void
    {
        $this->expectException(DeserializationException::class);

        SendMessageResponseBody::fromXml(new \SimpleXMLElement('<QueueMessagesList />'));
    }
}
