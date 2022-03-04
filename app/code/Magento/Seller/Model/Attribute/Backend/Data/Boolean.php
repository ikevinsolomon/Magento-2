<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Attribute\Backend\Data;

/**
 * Boolean seller attribute backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Boolean extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Prepare data before attribute save
     *
     * @param \Magento\Seller\Model\Seller $seller
     * @return $this
     */
    public function beforeSave($seller)
    {
        $attributeName = $this->getAttribute()->getName();
        $inputValue = $seller->getData($attributeName);
        $inputValue = $inputValue === null ? $this->getAttribute()->getDefaultValue() : $inputValue;
        $sanitizedValue = !empty($inputValue) ? '1' : '0';
        $seller->setData($attributeName, $sanitizedValue);
        return $this;
    }
}
