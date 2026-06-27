<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Sas;

/**
 * Defines the supported SAS protocol values.
 */
enum SasProtocol: string
{
    case HTTPS = 'https';
    case HTTPS_AND_HTTP = 'https,http';
}
