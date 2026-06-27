---
sidebar_position: 2
title: Shared access signature (SAS)
---

import SasAuthorizePage from '@site/src/components/docs/authorize/SasAuthorizePage';

<SasAuthorizePage
  supportsGeneration={false}
  usingIntro="This SDK supports SAS authentication, but does not support SAS generation yet."
  usingScopes={[
    {
      title: 'Single queue (Queue SAS URL)',
      code: `
<?php

use AzureOss\\Storage\\Queue\\QueueClient;
use GuzzleHttp\\Psr7\\Uri;

$queue = new QueueClient(new Uri(getenv('AZURE_QUEUE_SAS_URL')));
$messages = $queue->receiveMessages(5);
`,
    },
  ]}
  connectionStringIntro="If your connection string contains `SharedAccessSignature=...`, `QueueServiceClient::fromConnectionString()` uses it for authentication."
  connectionStringEnvExample={`
AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=https;AccountName=...;EndpointSuffix=core.windows.net;SharedAccessSignature=sv=...&ss=...&srt=...&sp=...&se=...&st=...&spr=https&sig=..."
`}
  connectionStringPhpExample={`
<?php

use AzureOss\\Storage\\Queue\\QueueServiceClient;

$service = QueueServiceClient::fromConnectionString(
    getenv('AZURE_STORAGE_CONNECTION_STRING')
);
`}
  endpointUrlIntro="You can also create a service client from a SAS endpoint URL by including the SAS query string in the URI."
  endpointUrlPhpExample={`
<?php

use AzureOss\\Storage\\Queue\\QueueServiceClient;
use GuzzleHttp\\Psr7\\Uri;

$endpoint = new Uri(getenv('AZURE_QUEUE_SAS_ENDPOINT')); // https://{account}.queue.core.windows.net/?sv=...&sig=...
$service = new QueueServiceClient($endpoint);
`}
  serviceClientUsagePhpExample={`
$queue = $service->getQueueClient('quickstart');
$messages = $queue->receiveMessages(5);
`}
  generationNotSupportedText="SAS generation is not supported yet in this SDK."
/>
