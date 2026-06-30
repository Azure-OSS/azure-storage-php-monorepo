<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Queue\Responses\ReceiveMessagesResponseBody;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReceiveMessagesResponseBodyTest extends TestCase
{
    #[Test]
    public function it_deserializes_messages_from_a_response(): void
    {
        $messages = ReceiveMessagesResponseBody::fromResponse(new Response(200, body: <<<'XML'
            <QueueMessagesList>
                <QueueMessage>
                    <MessageId>message-id</MessageId>
                    <InsertionTime>Sun, 27 Sep 2009 18:41:57 GMT</InsertionTime>
                    <ExpirationTime>Sun, 04 Oct 2009 18:41:57 GMT</ExpirationTime>
                    <PopReceipt>pop-receipt</PopReceipt>
                    <TimeNextVisible>Sun, 27 Sep 2009 18:42:27 GMT</TimeNextVisible>
                    <DequeueCount>3</DequeueCount>
                    <MessageText>hello world</MessageText>
                </QueueMessage>
            </QueueMessagesList>
            XML));

        self::assertCount(1, $messages);
        self::assertSame('message-id', $messages[0]->messageId);
        self::assertSame('hello world', $messages[0]->messageText);
    }

    #[Test]
    public function it_deserializes_messages_from_xml(): void
    {
        $body = ReceiveMessagesResponseBody::fromXml(new \SimpleXMLElement(<<<'XML'
            <QueueMessagesList>
                <QueueMessage>
                    <MessageId>message-id</MessageId>
                    <InsertionTime>Sun, 27 Sep 2009 18:41:57 GMT</InsertionTime>
                    <ExpirationTime>Sun, 04 Oct 2009 18:41:57 GMT</ExpirationTime>
                    <PopReceipt>pop-receipt</PopReceipt>
                    <TimeNextVisible>Sun, 27 Sep 2009 18:42:27 GMT</TimeNextVisible>
                    <DequeueCount>3</DequeueCount>
                    <MessageText>hello world</MessageText>
                </QueueMessage>
            </QueueMessagesList>
            XML));

        self::assertCount(1, $body->messages);
        self::assertSame('message-id', $body->messages[0]->messageId);
        self::assertSame('hello world', $body->messages[0]->messageText);
    }
}
