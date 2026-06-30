<?php

declare(strict_types=1);

namespace AzureOss\Tests\Storage\BlobFlysystemBundle\Unit;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\BlobFlysystem\AzureBlobStorageAdapter;
use AzureOss\Storage\BlobFlysystemBundle\AzureStorageBlobAdapterDefinitionBuilder;
use League\FlysystemBundle\Test\AbstractAdapterDefinitionBuilderTest;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class AzureStorageBlobAdapterDefinitionBuilderTest extends AbstractAdapterDefinitionBuilderTest
{
    protected function createBuilder(): AzureStorageBlobAdapterDefinitionBuilder
    {
        return new AzureStorageBlobAdapterDefinitionBuilder;
    }

    /**
     * @return \Generator<string, array{0: array<string, mixed>}>
     */
    public static function provideValidOptions(): \Generator
    {
        yield 'minimal' => [
            [
                'client' => 'my_client',
                'container' => 'my-container',
            ],
        ];

        yield 'full' => [
            [
                'client' => 'my_client',
                'container' => 'my-container',
                'prefix' => 'some/prefix',
                'mime_type_detector' => 'my.mime_type_detector',
                'visibility_handling' => AzureBlobStorageAdapter::ON_VISIBILITY_IGNORE,
                'public_container' => true,
            ],
        ];
    }

    protected function assertDefinition(Definition $definition): void
    {
        self::assertSame(
            AzureBlobStorageAdapter::class,
            $definition->getClass(),
        );

        // arg 0: reference to the per-storage BlobContainerClient definition
        $containerClientRef = $definition->getArgument(0);
        self::assertInstanceOf(Reference::class, $containerClientRef);
        self::assertSame(
            'flysystem.adapter.full.azure_oss_container_client',
            (string) $containerClientRef,
        );

        // arg 1: prefix
        self::assertSame('some/prefix', $definition->getArgument(1));

        // arg 2: mime type detector reference (the 'full' fixture supplies one)
        $mimeTypeDetectorRef = $definition->getArgument(2);
        self::assertInstanceOf(Reference::class, $mimeTypeDetectorRef);
        self::assertSame(
            'my.mime_type_detector',
            (string) $mimeTypeDetectorRef,
        );

        // arg 3: visibility handling
        self::assertSame(
            AzureBlobStorageAdapter::ON_VISIBILITY_IGNORE,
            $definition->getArgument(3),
        );

        // arg 4: public_container
        self::assertTrue($definition->getArgument(4));

        // Also verify the auxiliary container-client service is wired correctly.
        $container = $this->getContainer();
        self::assertTrue(
            $container->hasDefinition(
                'flysystem.adapter.full.azure_oss_container_client',
            ),
        );
        $containerClient = $container->getDefinition(
            'flysystem.adapter.full.azure_oss_container_client',
        );
        self::assertSame(
            BlobContainerClient::class,
            $containerClient->getClass(),
        );
        self::assertSame('my-container', $containerClient->getArgument(0));

        $factory = $containerClient->getFactory();
        self::assertIsArray($factory);
        self::assertInstanceOf(Reference::class, $factory[0]);
        self::assertSame('my_client', (string) $factory[0]);
        self::assertSame('getContainerClient', $factory[1]);
    }

    public function test_adapter_name_is_azure_oss(): void
    {
        self::assertSame('azure_oss', $this->createBuilder()->getName());
    }

    public function test_required_packages_points_at_the_flysystem_adapter(): void
    {
        self::assertSame(
            [
                AzureBlobStorageAdapter::class => 'azure-oss/storage-blob-flysystem',
            ],
            $this->createBuilder()->getRequiredPackages(),
        );
    }

    public function test_mime_type_detector_defaults_to_null_reference(): void
    {
        $builder = $this->createBuilder();
        $container = $this->getContainer();

        $adapterId = $builder->createAdapter(
            $container,
            'default',
            [
                'client' => 'my_client',
                'container' => 'my-container',
                'prefix' => '',
                'mime_type_detector' => null,
                'visibility_handling' => AzureBlobStorageAdapter::ON_VISIBILITY_THROW_ERROR,
                'public_container' => false,
            ],
            null,
        );

        self::assertNull($container->getDefinition($adapterId)->getArgument(2));
    }

    public function test_add_configuration_requires_an_array_node_definition(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Expected ArrayNodeDefinition');

        $this->createBuilder()->addConfiguration(new ScalarNodeDefinition('azure_oss'));
    }

    public function test_create_adapter_uses_default_optional_values(): void
    {
        $builder = $this->createBuilder();
        $container = $this->getContainer();

        $adapterId = $builder->createAdapter(
            $container,
            'defaulted',
            [
                'client' => 'my_client',
                'container' => 'my-container',
            ],
            null,
        );

        $definition = $container->getDefinition($adapterId);

        self::assertSame('', $definition->getArgument(1));
        self::assertNull($definition->getArgument(2));
        self::assertSame(
            AzureBlobStorageAdapter::ON_VISIBILITY_THROW_ERROR,
            $definition->getArgument(3),
        );
        self::assertFalse($definition->getArgument(4));
    }

    public function test_create_adapter_requires_client_to_be_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Option "client" for azure_oss adapter must be a string.',
        );

        $this->createBuilder()->createAdapter(
            $this->getContainer(),
            'invalid',
            [
                'client' => true,
                'container' => 'my-container',
            ],
            null,
        );
    }

    public function test_create_adapter_requires_container_option(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing required "container" option for azure_oss adapter.',
        );

        $this->createBuilder()->createAdapter(
            $this->getContainer(),
            'invalid',
            [
                'client' => 'my_client',
            ],
            null,
        );
    }

    public function test_create_adapter_rejects_invalid_optional_option_types(): void
    {
        try {
            $this->createBuilder()->createAdapter(
                $this->getContainer(),
                'invalid-prefix',
                [
                    'client' => 'my_client',
                    'container' => 'my-container',
                    'prefix' => false,
                ],
                null,
            );
            self::fail('Expected invalid prefix type to fail.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(
                'Option "prefix" for azure_oss adapter must be a string or null.',
                $exception->getMessage(),
            );
        }

        try {
            $this->createBuilder()->createAdapter(
                $this->getContainer(),
                'invalid-public',
                [
                    'client' => 'my_client',
                    'container' => 'my-container',
                    'public_container' => 'yes',
                ],
                null,
            );
            self::fail('Expected invalid public_container type to fail.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(
                'Option "public_container" for azure_oss adapter must be a boolean.',
                $exception->getMessage(),
            );
        }
    }
}
