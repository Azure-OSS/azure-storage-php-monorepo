<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Exceptions;

/**
 * Indicates that a Storage account key is not valid base64-encoded key material.
 */
final class InvalidAccountKeyException extends \Exception {}
