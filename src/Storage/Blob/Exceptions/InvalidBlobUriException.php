<?php

declare(strict_types=1);

namespace AzureOss\Storage\Blob\Exceptions;

/**
 * Indicates that a URI does not identify the required blob or container resource.
 */
final class InvalidBlobUriException extends \Exception {}
