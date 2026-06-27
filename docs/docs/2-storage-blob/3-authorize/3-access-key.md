---
sidebar_position: 3
title: Access Key
---

import AccessKeyAuthorizePage from '@site/src/components/docs/authorize/AccessKeyAuthorizePage';

<AccessKeyAuthorizePage
  serviceName="Azure Blob Storage"
  clientClassName="BlobServiceClient"
  clientFqn="AzureOss\\Storage\\Blob\\BlobServiceClient"
  endpointSubdomain="blob"
  verifySasCapabilitySnippet={`
$container = $service->getContainerClient('quickstart');

if ($container->canGenerateSasUri()) {
    // generate container/blob SAS tokens
}
`}
/>
