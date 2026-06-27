<?php

declare(strict_types=1);

namespace AzureOss\Identity;

/**
 * Indicates that a credential cannot attempt authentication in the current environment.
 */
class CredentialUnavailableException extends \RuntimeException
{
    /**
     * @param  list<self>  $exceptions
     */
    public static function createAggregateException(string $message, array $exceptions): self
    {
        if (count($exceptions) === 1) {
            return $exceptions[0];
        }

        $errorMessage = $message;
        foreach ($exceptions as $exception) {
            $errorMessage .= PHP_EOL.'- '.$exception->getMessage();
        }

        $innerException = new AggregateException(
            'Multiple exceptions were encountered while attempting to authenticate.',
            $exceptions,
        );

        return new self($errorMessage, previous: $innerException);
    }
}
