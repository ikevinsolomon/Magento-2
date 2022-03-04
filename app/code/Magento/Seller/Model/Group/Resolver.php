<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Group;

use Magento\Seller\Model\ResourceModel\Group\Resolver as ResolverResource;

/**
 * Lightweight service for getting current seller group based on seller id
 */
class Resolver
{
    /**
     * @var ResolverResource
     */
    private $resolverResource;

    /**
     * @param ResolverResource $resolverResource
     */
    public function __construct(ResolverResource $resolverResource)
    {
        $this->resolverResource = $resolverResource;
    }

    /**
     * Return seller group id
     *
     * @param int $sellerId
     * @return int|null
     */
    public function resolve(int $sellerId) : ?int
    {
        return $this->resolverResource->resolve($sellerId);
    }
}
