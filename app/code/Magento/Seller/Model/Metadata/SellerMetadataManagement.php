<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Metadata;

use Magento\Seller\Api\SellerMetadataManagementInterface;
use Magento\Seller\Api\Data\AttributeMetadataInterface;

/**
 * Service to manage seller related custom attributes
 */
class SellerMetadataManagement implements SellerMetadataManagementInterface
{
    /**
     * @var AttributeResolver
     */
    protected $attributeResolver;

    /**
     * @param AttributeResolver $attributeResolver
     */
    public function __construct(
        AttributeResolver $attributeResolver
    ) {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeSearchableInGrid(AttributeMetadataInterface $attribute)
    {
        return $this->attributeResolver->getModelByAttribute(self::ENTITY_TYPE_SELLER, $attribute)
            ->canBeSearchableInGrid();
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFilterableInGrid(AttributeMetadataInterface $attribute)
    {
        return $this->attributeResolver->getModelByAttribute(self::ENTITY_TYPE_SELLER, $attribute)
            ->canBeFilterableInGrid();
    }
}
