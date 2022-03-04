<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Metadata;

use Magento\Seller\Api\AddressMetadataInterface;

/**
 * Cached seller address attribute metadata
 */
class AddressCachedMetadata extends CachedMetadata implements AddressMetadataInterface
{
    /**
     * @var string
     */
    protected $entityType = 'seller_address';

    /**
     * Constructor
     *
     * @param AddressMetadata $metadata
     * @param AttributeMetadataCache|null $attributeMetadataCache
     */
    public function __construct(
        AddressMetadata $metadata,
        AttributeMetadataCache $attributeMetadataCache = null
    ) {
        parent::__construct($metadata, $attributeMetadataCache);
    }
}
