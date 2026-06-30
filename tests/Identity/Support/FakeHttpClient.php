<?php

declare(strict_types=1);

namespace AzureOss\Tests\Identity\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakeHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /**
     * @param  list<ResponseInterface|\Throwable>  $queue
     */
    public function __construct(private array $queue) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->queue === []) {
            throw new \RuntimeException('Unexpected HTTP request with no queued response.');
        }

        $next = array_shift($this->queue);

        if ($next instanceof \Throwable) {
            throw $next;
        }

        return $next;
    }
}
