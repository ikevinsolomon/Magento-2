<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Ui\Component\Listing;

use Magento\Seller\Api\AddressMetadataInterface;
use Magento\Seller\Api\AddressMetadataManagementInterface;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\SellerMetadataManagementInterface;
use Magento\Seller\Api\Data\AttributeMetadataInterface;
use Magento\Seller\Api\MetadataManagementInterface;
use Magento\Seller\Model\Indexer\Attribute\Filter;

/**
 * Attribute Repository Managment
 */
class AttributeRepository
{
    const BILLING_ADDRESS_PREFIX = 'billing_';

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var \Magento\Seller\Api\SellerMetadataInterface
     */
    protected $sellerMetadata;

    /**
     * @var \Magento\Seller\Api\AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var \Magento\Seller\Api\SellerMetadataManagementInterface
     */
    protected $sellerMetadataManagement;

    /**
     * @var \Magento\Seller\Api\AddressMetadataManagementInterface
     */
    protected $addressMetadataManagement;

    /**
     * @var \Magento\Seller\Model\Indexer\Attribute\Filter
     */
    protected $attributeFilter;

    /**
     * @param SellerMetadataManagementInterface $sellerMetadataManagement
     * @param AddressMetadataManagementInterface $addressMetadataManagement
     * @param SellerMetadataInterface $sellerMetadata
     * @param AddressMetadataInterface $addressMetadata
     * @param Filter $attributeFiltering
     */
    public function __construct(
        SellerMetadataManagementInterface $sellerMetadataManagement,
        AddressMetadataManagementInterface $addressMetadataManagement,
        SellerMetadataInterface $sellerMetadata,
        AddressMetadataInterface $addressMetadata,
        Filter $attributeFiltering
    ) {
        $this->sellerMetadataManagement = $sellerMetadataManagement;
        $this->addressMetadataManagement = $addressMetadataManagement;
        $this->sellerMetadata = $sellerMetadata;
        $this->addressMetadata = $addressMetadata;
        $this->attributeFilter = $attributeFiltering;
    }

    /**
     * Returns attribute list for current seller
     *
     * @return array
     */
    public function getList()
    {
        if (!$this->attributes) {
            $this->attributes = $this->getListForEntity(
                $this->sellerMetadata->getAllAttributesMetadata(),
                SellerMetadataInterface::ENTITY_TYPE_SELLER,
                $this->sellerMetadataManagement
            );
            $this->attributes = array_merge(
                $this->attributes,
                $this->getListForEntity(
                    $this->addressMetadata->getAllAttributesMetadata(),
                    AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    $this->addressMetadataManagement
                )
            );
        }

        return $this->attributeFilter->filter($this->attributes);
    }

    /**
     * Returns attribute list for given entity type code
     *
     * @param AttributeMetadataInterface[] $metadata
     * @param string $entityTypeCode
     * @param MetadataManagementInterface $management
     * @return array
     */
    protected function getListForEntity(array $metadata, $entityTypeCode, MetadataManagementInterface $management)
    {
        $attributes = [];
        /** @var AttributeMetadataInterface $attribute */
        foreach ($metadata as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($entityTypeCode == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
                $attributeCode = self::BILLING_ADDRESS_PREFIX . $attribute->getAttributeCode();
            }
            $attributes[$attributeCode] = [
                AttributeMetadataInterface::ATTRIBUTE_CODE => $attributeCode,
                AttributeMetadataInterface::FRONTEND_INPUT => $attribute->getFrontendInput(),
                AttributeMetadataInterface::FRONTEND_LABEL => $attribute->getFrontendLabel(),
                AttributeMetadataInterface::BACKEND_TYPE => $attribute->getBackendType(),
                AttributeMetadataInterface::OPTIONS => $this->getOptionArray($attribute->getOptions()),
                AttributeMetadataInterface::IS_USED_IN_GRID => $attribute->getIsUsedInGrid(),
                AttributeMetadataInterface::IS_VISIBLE_IN_GRID => $attribute->getIsVisibleInGrid(),
                AttributeMetadataInterface::IS_FILTERABLE_IN_GRID => $management->canBeFilterableInGrid($attribute),
                AttributeMetadataInterface::IS_SEARCHABLE_IN_GRID => $management->canBeSearchableInGrid($attribute),
                AttributeMetadataInterface::VALIDATION_RULES => $attribute->getValidationRules(),
                AttributeMetadataInterface::REQUIRED => $attribute->isRequired(),
                'entity_type_code' => $entityTypeCode,
            ];
        }

        return $attributes;
    }

    /**
     * Convert options to array
     *
     * @param array $options
     * @return array
     */
    protected function getOptionArray(array $options)
    {
        /** @var \Magento\Seller\Api\Data\OptionInterface $option */
        foreach ($options as &$option) {
            $value = $option->getValue();
            if (is_array($option->getOptions())) {
                $value = $this->getOptionArray($option->getOptions());
            }
            $option = [
                'label' => (string)$option->getLabel(),
                'value' => $value,
                '__disableTmpl' => true
            ];
        }
        return $options;
    }

    /**
     * Return seller group's metadata by given group code
     *
     * @param string $code
     * @return array | null
     */
    public function getMetadataByCode($code)
    {
        return $this->getList()[$code] ?? null;
    }
}
