<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Block\Widget;

use Magento\Seller\Api\SellerMetadataInterface;

/**
 * Seller Value Added Tax Widget
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Taxvat extends AbstractWidget
{
    /**
     * Constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Seller\Helper\Address $addressHelper
     * @param SellerMetadataInterface $sellerMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Seller\Helper\Address $addressHelper,
        SellerMetadataInterface $sellerMetadata,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $sellerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Sets the template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Seller::widget/taxvat.phtml');
    }

    /**
     * Get is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute('taxvat') ? (bool)$this->_getAttribute('taxvat')->isVisible() : false;
    }

    /**
     * Get is required.
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute('taxvat') ? (bool)$this->_getAttribute('taxvat')->isRequired() : false;
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
}
