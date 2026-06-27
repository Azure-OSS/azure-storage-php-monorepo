<?php

declare(strict_types=1);

namespace AzureOss\Storage\Tests\Common\Feature;

use AzureOss\Storage\Common\ApiVersion;
use AzureOss\Storage\Common\Middleware\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Server\Server;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClientFactoryApiVersionTest extends TestCase
{
    protected function setUp(): void
    {
        Server::start();
    }

    protected function tearDown(): void
    {
        Server::stop();
    }

    #[Test]
    public function azurite_endpoint_uses_latest_azurite_version(): void
    {
        Server::enqueue([new Response]);

        $client = (new ClientFactory)->create(
            uri: new Uri('http://127.0.0.1:10000/devstoreaccount1'),
        );
        $client->get($this->serverUrl());

        $requests = Server::received();

        self::assertCount(1, $requests);
        self::assertSame(ApiVersion::latestAzurite()->value, $requests[0]->getHeaderLine('x-ms-version'));
    }

    #[Test]
    public function explicitly_configured_version_takes_precedence_for_azurite_endpoint(): void
    {
        Server::enqueue([new Response]);

        $client = (new ClientFactory)->create(
            uri: new Uri('http://127.0.0.1:10000/devstoreaccount1'),
            apiVersion: ApiVersion::V2024_08_04,
        );
        $client->get($this->serverUrl());

        $requests = Server::received();

        self::assertCount(1, $requests);
        self::assertSame(ApiVersion::V2024_08_04->value, $requests[0]->getHeaderLine('x-ms-version'));
    }

    private function serverUrl(): string
    {
        if (! is_string(Server::$url)) {
            self::fail('The test server URL was not initialized.');
        }

        return Server::$url;
    }
}
