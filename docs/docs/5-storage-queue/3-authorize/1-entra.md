---
sidebar_position: 1
title: Microsoft Entra ID
---

import EntraIdAuthorizePage from '@site/src/components/docs/authorize/EntraIdAuthorizePage';

<EntraIdAuthorizePage
  serviceName="Azure Storage Queues"
  clientClassName="QueueServiceClient"
  clientFqn="AzureOss\\Storage\\Queue\\QueueServiceClient"
  endpointSubdomain="queue"
  dataContributorRole="Storage Queue Data Contributor"
  dataReaderRole="Storage Queue Data Reader"
  notes={[
    'Token-based credentials cannot sign SAS tokens. For SAS-based workflows, use access keys (shared key) to create SAS tokens outside this SDK.',
  ]}
/>
