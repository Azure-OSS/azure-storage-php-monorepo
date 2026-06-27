<?php

declare(strict_types=1);

namespace AzureOss\Identity;

/**
 * Tries a configurable chain of common Azure credentials.
 *
 * By default the chain checks environment and workload identity configuration;
 * managed identity can be enabled through {@see DefaultAzureCredentialOptions}.
 */
final class DefaultAzureCredential implements TokenCredential
{
    private TokenCredential $chain;

    /** Creates the default credential chain from the enabled credential sources. */
    public function __construct(
        private readonly DefaultAzureCredentialOptions $options = new DefaultAzureCredentialOptions
    ) {
        $sources = [];

        if (! $this->options->excludeEnvironmentCredential) {
            $sources[] = new EnvironmentCredential(
                new EnvironmentCredentialOptions(
                    authorityHost: $this->options->authorityHost,
                    httpClient: $this->options->httpClient,
                    requestFactory: $this->options->requestFactory,
                    streamFactory: $this->options->streamFactory,
                )
            );
        }

        if (! $this->options->excludeWorkloadIdentityCredential) {
            $sources[] = new WorkloadIdentityCredential(
                new WorkloadIdentityCredentialOptions(
                    authorityHost: $this->options->authorityHost,
                    httpClient: $this->options->httpClient,
                    requestFactory: $this->options->requestFactory,
                    streamFactory: $this->options->streamFactory,
                )
            );
        }

        if (! $this->options->excludeManagedIdentityCredential) {
            $sources[] = new ManagedIdentityCredential(
                new ManagedIdentityCredentialOptions(
                    authorityHost: $this->options->authorityHost,
                    httpClient: $this->options->httpClient,
                    requestFactory: $this->options->requestFactory,
                    streamFactory: $this->options->streamFactory,
                )
            );
        }

        $this->chain = new ChainedTokenCredential($sources);
    }

    public function getToken(TokenRequestContext $context): AccessToken
    {
        return $this->chain->getToken($context);
    }
}
