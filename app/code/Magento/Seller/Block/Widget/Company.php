<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Block\Widget;

use Magento\Seller\Api\AddressMetadataInterface;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Helper\Address as AddressHelper;
use Magento\Seller\Model\Options;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing seller company.
 *
 * @method SellerInterface getObject()
 * @method Name setObject(SellerInterface $seller)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Company extends AbstractWidget
{

    /**
     * the attribute code
     */
    const ATTRIBUTE_CODE = 'company';

    /**
     * @var AddressMetadataInterface
     */
    protected $addressMetadata;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @param Context $context
     * @param AddressHelper $addressHelper
     * @param SellerMetadataInterface $sellerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        SellerMetadataInterface $sellerMetadata,
        Options $options,
        AddressMetadataInterface $addressMetadata,
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct($context, $addressHelper, $sellerMetadata, $data);
        $this->addressMetadata = $addressMetadata;
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        // default template location
        $this->setTemplate('Magento_Seller::widget/company.phtml');
    }

    /**
     * Can show config value
     *
     * @param string $key
     *
     * @return bool
     */
    protected function _showConfig($key)
    {
        return (bool)$this->getConfig($key);
    }

    /**
     * Can show prefix
     *
     * @return bool
     */
    public function showCompany()
    {
        return $this->_isAttributeVisible(self::ATTRIBUTE_CODE);
    }

    /**
     * @inheritdoc
     */
    protected function _getAttribute($attributeCode)
    {
        if ($this->getForceUseSellerAttributes() || $this->getObject() instanceof SellerInterface) {
            return parent::_getAttribute($attributeCode);
        }

        try {
            $attribute = $this->addressMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        if ($this->getForceUseSellerRequiredAttributes() && $attribute && !$attribute->isRequired()) {
            $sellerAttribute = parent::_getAttribute($attributeCode);
            if ($sellerAttribute && $sellerAttribute->isRequired()) {
                $attribute = $sellerAttribute;
            }
        }

        return $attribute;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get string with frontend validation classes for attribute
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getAttributeValidationClass($attributeCode)
    {
        return $this->_addressHelper->getAttributeValidationClass($attributeCode);
    }

    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    private function _isAttributeVisible($attributeCode)
    {
        $attributeMetadata = $this->_getAttribute($attributeCode);
        return $attributeMetadata ? (bool)$attributeMetadata->isVisible() : false;
    }

    /**
     * Check if company attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE) ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)->isVisible(
        ) : false;
    }

    /**
     * Check if company attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute(self::ATTRIBUTE_CODE) ? (bool)$this->_getAttribute(self::ATTRIBUTE_CODE)
            ->isRequired() : false;
    }
}
