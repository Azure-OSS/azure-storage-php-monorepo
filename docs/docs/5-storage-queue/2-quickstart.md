---
sidebar_position: 2
title: Quickstart
---

This quickstart walks through the minimum flow: connect, create a queue, send a message, receive it, and delete it.

## Before You Start

Set your connection string:

```bash
export AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=...;AccountName=...;AccountKey=...;EndpointSuffix=core.windows.net"
```

## End-To-End Example

```php
<?php

use AzureOss\Storage\Queue\QueueServiceClient;

$service = QueueServiceClient::fromConnectionString(
    getenv('AZURE_STORAGE_CONNECTION_STRING')
);

$queue = $service->getQueueClient('quickstart');
$queue->createIfNotExists();

$queue->sendMessage('Hello from Azure-OSS');

$message = $queue->receiveMessage(30);
if ($message !== null) {
    echo $message->messageText.PHP_EOL;
    $queue->deleteMessage($message->messageId, $message->popReceipt);
}

// Optional cleanup
$queue->deleteIfExists();
```

## Next Steps

- Explore authorization options in the `Authorize` section.
