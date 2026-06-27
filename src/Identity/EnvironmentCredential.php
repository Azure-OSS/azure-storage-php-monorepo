<?php

declare(strict_types=1);

namespace AzureOss\Identity;

final class EnvironmentCredential implements TokenCredential
{
    public function __construct(
        private readonly EnvironmentCredentialOptions $options = new EnvironmentCredentialOptions
    ) {}

    public function getToken(TokenRequestContext $context): AccessToken
    {
        $credential = $this->getCredential();

        if ($credential === null) {
            throw new CredentialUnavailableException;
        }

        return $credential->getToken($context);
    }

    private function getCredential(): ?TokenCredential
    {
        $tenantId = getenv('AZURE_TENANT_ID');
        $clientId = getenv('AZURE_CLIENT_ID');
        if ($tenantId === false || $clientId === false) {
            return null;
        }

        $clientSecret = getenv('AZURE_CLIENT_SECRET');
        if ($clientSecret !== false) {
            return new ClientSecretCredential(
                $tenantId,
                $clientId,
                $clientSecret,
                new ClientSecretCredentialOptions(
                    authorityHost: $this->options->authorityHost,
                    httpClient: $this->options->httpClient,
                    requestFactory: $this->options->requestFactory,
                    streamFactory: $this->options->streamFactory,
                )
            );
        }

        $clientCertificatePath = getenv('AZURE_CLIENT_CERTIFICATE_PATH');
        if ($clientCertificatePath !== false) {
            $clientCertificatePassword = getenv('AZURE_CLIENT_CERTIFICATE_PASSWORD');

            return new ClientCertificateCredential(
                $tenantId,
                $clientId,
                $clientCertificatePath,
                $clientCertificatePassword !== false ? $clientCertificatePassword : null,
                new ClientCertificateCredentialOptions(
                    authorityHost: $this->options->authorityHost,
                    httpClient: $this->options->httpClient,
                    requestFactory: $this->options->requestFactory,
                    streamFactory: $this->options->streamFactory,
                )
            );
        }

        return null;
    }
}
