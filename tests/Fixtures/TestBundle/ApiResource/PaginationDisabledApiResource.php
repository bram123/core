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

namespace ApiPlatform\Tests\Fixtures\TestBundle\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource]
#[Get(uriTemplate: '/pagination_disabled_resources/{id}')]
#[GetCollection(
    uriTemplate: '/pagination_disabled_resources',
    paginationEnabled: false,
    provider: [self::class, 'provide']
)]
final class PaginationDisabledApiResource
{
    public function __construct(
        public ?int    $id = null,
        public ?string $name = null
    )
    {
    }

    /**
     * @return list<self>
     */
    public static function provide(): array
    {
        return [
            new self(1, 'Item 1'),
            new self(2, 'Item 2'),
        ];
    }
}
