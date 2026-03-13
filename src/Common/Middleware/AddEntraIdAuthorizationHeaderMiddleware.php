<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Middleware;

use AzureOss\Identity\AccessToken;
use AzureOss\Identity\TokenCredential;
use AzureOss\Identity\TokenRequestContext;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class AddEntraIdAuthorizationHeaderMiddleware
{
    private readonly TokenRequestContext $tokenRequestContext;

    private ?AccessToken $cachedAccessToken = null;

    public function __construct(private readonly TokenCredential $tokenCredential)
    {
        $this->tokenRequestContext = new TokenRequestContext(['https://storage.azure.com/.default']);
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($this->cachedAccessToken === null ||
                $this->expiresInAMinute($this->cachedAccessToken)
            ) {
                $this->cachedAccessToken = $this->tokenCredential->getToken($this->tokenRequestContext);
            }

            $request = $request->withHeader('Authorization', $this->cachedAccessToken->tokenType.' '.$this->cachedAccessToken->token);

            return $handler($request, $options);
        };
    }

    private function expiresInAMinute(AccessToken $accessToken): bool
    {
        return $accessToken->expiresOn < (new \DateTimeImmutable)->add(new \DateInterval('PT1M'));
    }
}
