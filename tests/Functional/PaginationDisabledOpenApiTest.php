<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Tests\Functional;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Tests\Fixtures\TestBundle\ApiResource\PaginationDisabledApiResource;
use ApiPlatform\Tests\SetupClassResourcesTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PaginationDisabledOpenApiKernel extends \AppKernel
{
    public function getCacheDir(): string
    {
        return parent::getCacheDir() . '/pagination_disabled_openapi';
    }

    public function getLogDir(): string
    {
        return parent::getLogDir() . '/pagination_disabled_openapi';
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        parent::configureContainer($c, $loader);

        $loader->load(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('api_platform', [
                'docs_formats' => [
                    'jsonopenapi' => ['application/vnd.openapi+json'],
                ],
                'defaults' => [
                    'pagination_client_enabled' => true,
                    'pagination_client_partial' => true,
                ],
            ]);
        });
    }
}

final class PaginationDisabledOpenApiTest extends ApiTestCase
{
    use SetupClassResourcesTrait;

    protected static ?bool $alwaysBootKernel = true;

    /**
     * @return class-string[]
     */
    public static function getResources(): array
    {
        return [PaginationDisabledApiResource::class];
    }

    protected static function getKernelClass(): string
    {
        return PaginationDisabledOpenApiKernel::class;
    }

    public function testOpenApiParametersWhenPaginationDisabled(): void
    {
        $response = self::createClient()->request('GET', '/docs.jsonopenapi', ['headers' => ['Accept' => 'application/vnd.openapi+json']]);
        $this->assertResponseIsSuccessful();

        $docs = $response->toArray();
        $this->assertArrayHasKey('paths', $docs);
        $this->assertArrayHasKey('/pagination_disabled_resources', $docs['paths']);
        $this->assertArrayHasKey('get', $docs['paths']['/pagination_disabled_resources']);

        $operation = $docs['paths']['/pagination_disabled_resources']['get'];
        $parameters = $operation['parameters'] ?? [];

        $parameterNames = array_column($parameters, 'name');
        $this->assertNotContains('page', $parameterNames);
        $this->assertNotContains('itemsPerPage', $parameterNames);
        $this->assertNotContains('pagination', $parameterNames);
        $this->assertNotContains('partial', $parameterNames);
    }

    public function testCollectionEndpointWhenPaginationDisabled(): void
    {
        self::createClient()->request('GET', '/pagination_disabled_resources', [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonEquals([
            '@context' => '/contexts/PaginationDisabledApiResource',
            '@id' => '/pagination_disabled_resources',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 2,
            'hydra:member' => [
                [
                    '@id' => '/pagination_disabled_resources/1',
                    '@type' => 'PaginationDisabledApiResource',
                    'id' => 1,
                    'name' => 'Item 1',
                ],
                [
                    '@id' => '/pagination_disabled_resources/2',
                    '@type' => 'PaginationDisabledApiResource',
                    'id' => 2,
                    'name' => 'Item 2',
                ],
            ],
        ]);
    }
}
