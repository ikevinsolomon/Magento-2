<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Metadata;

use Magento\Seller\Api\SellerMetadataInterface;

/**
 * Cached seller attribute metadata service
 */
class SellerCachedMetadata extends CachedMetadata implements SellerMetadataInterface
{
    /**
     * @var string
     */
    protected $entityType = 'seller';

    /**
     * Constructor
     *
     * @param SellerMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        SellerMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
