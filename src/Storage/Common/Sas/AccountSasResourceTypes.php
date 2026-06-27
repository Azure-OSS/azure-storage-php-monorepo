<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Sas;

/**
 * Selects the service, container, and object resource types granted by an account SAS.
 */
final class AccountSasResourceTypes
{
    public function __construct(
        public bool $service = false,
        public bool $container = false,
        public bool $object = false,
    ) {}

    public function __toString(): string
    {
        $permissions = '';

        if ($this->service) {
            $permissions .= 's';
        }
        if ($this->container) {
            $permissions .= 'c';
        }
        if ($this->object) {
            $permissions .= 'o';
        }

        return $permissions;
    }
}
