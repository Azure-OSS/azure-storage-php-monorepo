<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\QueueLaravel\Unit;

use AzureOss\Storage\QueueLaravel\AzureStorageQueueConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AzureStorageQueueConfigTest extends TestCase
{
    #[Test]
    public function validate_accepts_connection_string_configuration(): void
    {
        $config = $this->connectionStringConfig([
            'retry_after' => 30,
            'time_to_live' => 600,
            'timeout' => 10,
            'after_commit' => true,
            'create_queue' => false,
        ]);

        AzureStorageQueueConfig::validate($config);

        self::assertSame('jobs', $config['queue']);
    }

    #[Test]
    public function validate_accepts_shared_key_configuration(): void
    {
        $config = $this->sharedKeyConfig();

        AzureStorageQueueConfig::validate($config);

        self::assertSame('jobs', $config['queue']);
    }

    #[Test]
    public function validate_requires_queue_name(): void
    {
        $config = $this->genericConfig([
            'connection_string' => 'UseDevelopmentStorage=true',
            'queue' => null,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The [queue] must be a string');

        AzureStorageQueueConfig::validate($config);
    }

    #[Test]
    public function validate_requires_either_connection_string_or_shared_key_credentials(): void
    {
        $config = $this->genericConfig([
            'queue' => 'jobs',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Either [connection_string] or [account_name] + [account_key] must be provided');

        AzureStorageQueueConfig::validate($config);
    }

    #[Test]
    public function validate_rejects_invalid_scalar_types(): void
    {
        $config = $this->connectionStringConfig([
            'retry_after' => '30',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The [retry_after] must be an integer');

        AzureStorageQueueConfig::validate($config);
    }

    #[Test]
    public function validate_rejects_invalid_boolean_types(): void
    {
        $config = $this->connectionStringConfig([
            'after_commit' => 'yes',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The [after_commit] must be a boolean');

        AzureStorageQueueConfig::validate($config);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function connectionStringConfig(array $overrides = []): array
    {
        return $this->genericConfig(array_merge([
            'connection_string' => 'UseDevelopmentStorage=true',
            'queue' => 'jobs',
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function sharedKeyConfig(array $overrides = []): array
    {
        return $this->genericConfig(array_merge([
            'protocol' => 'http',
            'endpoint_suffix' => 'example.test',
            'account_name' => 'example',
            'account_key' => 'secret',
            'queue' => 'jobs',
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function genericConfig(array $config): array
    {
        return $config;
    }
}
