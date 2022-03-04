<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Block\Widget;

use Magento\Seller\Api\SellerMetadataInterface;

class AbstractWidget extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SellerMetadataInterface
     */
    protected $sellerMetadata;

    /**
     * @var \Magento\Seller\Helper\Address
     */
    protected $_addressHelper;

    /**
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
        $this->_addressHelper = $addressHelper;
        $this->sellerMetadata = $sellerMetadata;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function getConfig($key)
    {
        return $this->_addressHelper->getConfig($key);
    }

    /**
     * @return string
     */
    public function getFieldIdFormat()
    {
        if (!$this->hasData('field_id_format')) {
            $this->setData('field_id_format', '%s');
        }
        return $this->getData('field_id_format');
    }

    /**
     * @return string
     */
    public function getFieldNameFormat()
    {
        if (!$this->hasData('field_name_format')) {
            $this->setData('field_name_format', '%s');
        }
        return $this->getData('field_name_format');
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFieldId($field)
    {
        return sprintf($this->getFieldIdFormat(), $field);
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFieldName($field)
    {
        return sprintf($this->getFieldNameFormat(), $field);
    }

    /**
     * Retrieve seller attribute instance
     *
     * @param string $attributeCode
     * @return \Magento\Seller\Api\Data\AttributeMetadataInterface|null
     */
    protected function _getAttribute($attributeCode)
    {
        try {
            return $this->sellerMetadata->getAttributeMetadata($attributeCode);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
