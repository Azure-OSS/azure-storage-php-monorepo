<?php

declare(strict_types=1);

namespace AzureOss\Storage\Queue\Exceptions;

/**
 * Indicates that an Azure Queue Storage response could not be deserialized.
 */
final class DeserializationException extends \RuntimeException {}
