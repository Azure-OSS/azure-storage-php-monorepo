---
sidebar_position: 2
title: Shared access signature (SAS)
---

import SasAuthorizePage from '@site/src/components/docs/authorize/SasAuthorizePage';

<SasAuthorizePage
  supportsGeneration={true}
  usingIntro="This SDK supports SAS authentication."
  usingScopes={[
    {
      title: 'Single blob (Blob SAS URL)',
      code: `
<?php

use AzureOss\\Storage\\Blob\\BlobClient;
use GuzzleHttp\\Psr7\\Uri;

$blob = new BlobClient(new Uri(getenv('AZURE_BLOB_SAS_URL')));
$content = $blob->downloadStreaming()->content->getContents();
`,
    },
    {
      title: 'Container (Container SAS URL)',
      code: `
<?php

use AzureOss\\Storage\\Blob\\BlobContainerClient;
use GuzzleHttp\\Psr7\\Uri;

$container = new BlobContainerClient(new Uri(getenv('AZURE_BLOB_CONTAINER_SAS_URL')));

foreach ($container->getBlobs() as $item) {
    echo $item->name.PHP_EOL;
}
`,
    },
  ]}
  connectionStringIntro="If your connection string contains `SharedAccessSignature=...`, `BlobServiceClient::fromConnectionString()` uses it for authentication."
  connectionStringEnvExample={`
AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=https;AccountName=...;EndpointSuffix=core.windows.net;SharedAccessSignature=sv=...&ss=...&srt=...&sp=...&se=...&st=...&spr=https&sig=..."
`}
  connectionStringPhpExample={`
<?php

use AzureOss\\Storage\\Blob\\BlobServiceClient;

$service = BlobServiceClient::fromConnectionString(
    getenv('AZURE_STORAGE_CONNECTION_STRING')
);
`}
  endpointUrlIntro="You can also create a service client from a SAS endpoint URL by including the SAS query string in the URI."
  endpointUrlPhpExample={`
<?php

use AzureOss\\Storage\\Blob\\BlobServiceClient;
use GuzzleHttp\\Psr7\\Uri;

$endpoint = new Uri(getenv('AZURE_BLOB_SAS_ENDPOINT')); // https://{account}.blob.core.windows.net/?sv=...&sig=...
$service = new BlobServiceClient($endpoint);
`}
  serviceClientUsagePhpExample={`
$container = $service->getContainerClient('quickstart');
$blob = $container->getBlobClient('hello.txt');

$content = $blob->downloadStreaming()->content->getContents();
`}
  generationIntro="SAS generation requires credentials that can sign SAS tokens (shared key). Token-based authentication (Entra ID) cannot generate SAS in this SDK."
  generationSections={[
    {
      title: 'Generate a blob SAS URL',
      code: `
<?php

use AzureOss\\Storage\\Blob\\BlobServiceClient;
use AzureOss\\Storage\\Blob\\Sas\\BlobSasBuilder;

$service = BlobServiceClient::fromConnectionString(getenv('AZURE_STORAGE_CONNECTION_STRING'));
$blob = $service->getContainerClient('quickstart')->getBlobClient('hello.txt');

$blobSas = $blob->generateSasUri(
    BlobSasBuilder::new()
        ->setPermissions('r')
        ->setExpiresOn(new \\DateTimeImmutable('+15 minutes'))
);
`,
    },
    {
      title: 'Generate a container SAS URL',
      code: `
use AzureOss\\Storage\\Blob\\Sas\\BlobSasBuilder;

$container = $service->getContainerClient('quickstart');

$containerSas = $container->generateSasUri(
    BlobSasBuilder::new()
        ->setPermissions('rl')
        ->setExpiresOn(new \\DateTimeImmutable('+15 minutes'))
);
`,
    },
    {
      title: 'Generate an account SAS URL',
      code: `
use AzureOss\\Storage\\Common\\Sas\\AccountSasBuilder;
use AzureOss\\Storage\\Common\\Sas\\AccountSasPermissions;
use AzureOss\\Storage\\Common\\Sas\\AccountSasResourceTypes;

$accountSas = $service->generateAccountSasUri(
    AccountSasBuilder::new()
        ->setPermissions(new AccountSasPermissions(list: true, read: true))
        ->setResourceTypes(new AccountSasResourceTypes(service: true, container: true, object: true))
        ->setExpiresOn(new \\DateTimeImmutable('+15 minutes'))
);
`,
    },
  ]}
/>
