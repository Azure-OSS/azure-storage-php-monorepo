<?php

declare(strict_types=1);

namespace AzureOss\Storage\Queue;

use AzureOss\Identity\TokenCredential;
use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use AzureOss\Storage\Common\Helpers\ConnectionStringHelper;
use AzureOss\Storage\Queue\Exceptions\InvalidConnectionStringException;
use AzureOss\Storage\Queue\Models\QueueClientOptions;
use AzureOss\Storage\Queue\Models\QueueServiceClientOptions;
use Psr\Http\Message\UriInterface;

/**
 * Provides service-level access to queues in an Azure Storage account.
 */
final class QueueServiceClient
{
    /**
     * @param  UriInterface  $uri  Queue service endpoint, including any SAS query string.
     * @param  StorageSharedKeyCredential|TokenCredential|null  $credential  Credential used to authorize requests, or null for SAS access.
     * @param  QueueServiceClientOptions  $options  Client transport and service-version options.
     */
    public function __construct(
        public UriInterface $uri,
        public readonly StorageSharedKeyCredential|TokenCredential|null $credential = null,
        private readonly QueueServiceClientOptions $options = new QueueServiceClientOptions,
    ) {
        // must always include the forward slash (/) to separate the host name from the path and query portions of the URI.
        $this->uri = $uri->withPath(rtrim($uri->getPath(), '/').'/');
    }

    /**
     * Creates a client from an Azure Storage connection string.
     *
     * @throws InvalidConnectionStringException When the connection string does not contain a usable Queue endpoint and credential.
     */
    public static function fromConnectionString(string $connectionString, QueueServiceClientOptions $options = new QueueServiceClientOptions): self
    {
        $uri = ConnectionStringHelper::getQueueEndpoint($connectionString);
        if ($uri === null) {
            throw new InvalidConnectionStringException;
        }

        $sas = ConnectionStringHelper::getSas($connectionString);
        if ($sas !== null) {
            return new self($uri->withQuery($sas), options: $options);
        }

        $accountName = ConnectionStringHelper::getAccountName($connectionString);
        $accountKey = ConnectionStringHelper::getAccountKey($connectionString);
        if ($accountName !== null && $accountKey !== null) {
            return new self($uri, new StorageSharedKeyCredential($accountName, $accountKey), $options);
        }

        throw new InvalidConnectionStringException;
    }

    /**
     * Creates a client for the named queue without making a service request.
     */
    public function getQueueClient(string $queueName): QueueClient
    {
        return new QueueClient(
            $this->uri->withPath($this->uri->getPath().$queueName),
            $this->credential,
            new QueueClientOptions($this->options->httpClientOptions, $this->options->apiVersion),
        );
    }
}
