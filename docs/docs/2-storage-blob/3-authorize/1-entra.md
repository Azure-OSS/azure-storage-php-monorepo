---
sidebar_position: 1
title: Microsoft Entra ID
---

import EntraIdAuthorizePage from '@site/src/components/docs/authorize/EntraIdAuthorizePage';

<EntraIdAuthorizePage
  serviceName="Azure Blob Storage"
  clientClassName="BlobServiceClient"
  clientFqn="AzureOss\\Storage\\Blob\\BlobServiceClient"
  endpointSubdomain="blob"
  dataContributorRole="Storage Blob Data Contributor"
  dataReaderRole="Storage Blob Data Reader"
  notes={[
    "Token-based credentials cannot generate SAS URLs in this SDK (`canGenerateSasUri()` returns `false`).",
    'For SAS-based workflows, use access keys instead.',
  ]}
/>
