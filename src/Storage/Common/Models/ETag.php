<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Models;

final class ETag
{
    public function __construct(
        private readonly string $value,
    ) {}

    public static function all(): self
    {
        return new self('*');
    }

    public function equals(self|string $other): bool
    {
        return $this->value === (string) $other;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
