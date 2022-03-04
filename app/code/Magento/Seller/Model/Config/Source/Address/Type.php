<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Source model of seller address types
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Seller\Model\Config\Source\Address;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve possible seller address types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            \Magento\Seller\Model\Address\AbstractAddress::TYPE_BILLING => __('Billing Address'),
            \Magento\Seller\Model\Address\AbstractAddress::TYPE_SHIPPING => __('Shipping Address')
        ];
    }
}
