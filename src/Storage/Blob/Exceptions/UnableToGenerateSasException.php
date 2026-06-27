<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Exceptions;

/**
 * Indicates that a SAS cannot be signed because no shared-key credential is available.
 */
final class UnableToGenerateSasException extends \Exception {}
