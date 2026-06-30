<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\Queue\Unit;

use AzureOss\Storage\Queue\Exceptions\DeserializationException;
use AzureOss\Storage\Queue\Responses\UpdateMessageResponseBody;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdateMessageResponseBodyTest extends TestCase
{
    #[Test]
    public function it_deserializes_a_receipt_from_response_headers_when_the_body_is_empty(): void
    {
        $receipt = UpdateMessageResponseBody::fromResponse(new Response(204, [
            'x-ms-popreceipt' => 'updated-pop-receipt',
            'x-ms-time-next-visible' => 'Sun, 27 Sep 2009 18:43:57 GMT',
        ]));

        self::assertSame('updated-pop-receipt', $receipt->popReceipt);
        self::assertSame('2009-09-27T18:43:57+00:00', $receipt->timeNextVisible->format(\DateTimeInterface::ATOM));
    }

    #[Test]
    public function it_deserializes_a_receipt_from_an_xml_response_body(): void
    {
        $receipt = UpdateMessageResponseBody::fromResponse(new Response(204, body: <<<'XML'
            <QueueMessagesList>
                <QueueMessage>
                    <PopReceipt>updated-pop-receipt</PopReceipt>
                    <TimeNextVisible>Sun, 27 Sep 2009 18:43:57 GMT</TimeNextVisible>
                </QueueMessage>
            </QueueMessagesList>
            XML));

        self::assertSame('updated-pop-receipt', $receipt->popReceipt);
        self::assertSame('2009-09-27T18:43:57+00:00', $receipt->timeNextVisible->format(\DateTimeInterface::ATOM));
    }

    #[Test]
    public function it_rejects_malformed_xml_without_a_queue_message(): void
    {
        $this->expectException(DeserializationException::class);

        UpdateMessageResponseBody::fromXml(new \SimpleXMLElement('<QueueMessagesList />'));
    }
}
