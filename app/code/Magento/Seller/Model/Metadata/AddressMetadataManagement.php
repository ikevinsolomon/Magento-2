<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Metadata;

use Magento\Seller\Api\AddressMetadataManagementInterface;
use Magento\Seller\Api\Data\AttributeMetadataInterface;

/**
 * Service to manage seller address related custom attributes
 */
class AddressMetadataManagement implements AddressMetadataManagementInterface
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
        return $this->attributeResolver->getModelByAttribute(self::ENTITY_TYPE_ADDRESS, $attribute)
            ->canBeSearchableInGrid();
    }

    /**
     * {@inheritdoc}
     */
    public function canBeFilterableInGrid(AttributeMetadataInterface $attribute)
    {
        return $this->attributeResolver->getModelByAttribute(self::ENTITY_TYPE_ADDRESS, $attribute)
            ->canBeFilterableInGrid();
    }
}
