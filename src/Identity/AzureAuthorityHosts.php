<?php

declare(strict_types=1);

namespace AzureOss\Identity;

/**
 * Provides Microsoft Entra authority host names for Azure cloud environments.
 */
final class AzureAuthorityHosts
{
    const AZURE_PUBLIC_CLOUD = 'login.microsoftonline.com';

    const AZURE_CHINA = 'login.chinacloudapi.cn';

    const AZURE_US_GOVERNMENT = 'login.microsoftonline.us';
}
