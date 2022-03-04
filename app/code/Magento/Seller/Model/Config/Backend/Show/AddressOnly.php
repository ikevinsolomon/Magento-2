<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Config\Backend\Show;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Seller Show Address Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class AddressOnly extends Seller
{
    /**
     * Retrieve attribute objects
     *
     * @return AbstractAttribute[]
     */
    protected function _getAttributeObjects()
    {
        return [$this->_eavConfig->getAttribute('seller_address', $this->_getAttributeCode())];
    }
}
