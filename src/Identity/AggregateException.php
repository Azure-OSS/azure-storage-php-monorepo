<?php

declare(strict_types=1);

namespace AzureOss\Identity;

/**
 * Collects multiple failures produced while trying a credential chain.
 */
final class AggregateException extends \RuntimeException
{
    /**
     * @param  list<\Throwable>  $exceptions
     */
    public function __construct(
        string $message,
        public readonly array $exceptions = [],
    ) {
        parent::__construct($message, previous: $exceptions[0] ?? null);
    }
}
