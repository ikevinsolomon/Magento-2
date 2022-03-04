<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel\Address;

/**
 * Sellers collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Collection extends \Magento\Eav\Model\Entity\Collection\VersionControl\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Seller\Model\Address::class, \Magento\Seller\Model\ResourceModel\Address::class);
    }

    /**
     * Set seller filter
     *
     * @param \Magento\Seller\Model\Seller|array $seller
     * @return $this
     */
    public function setSellerFilter($seller)
    {
        if (is_array($seller)) {
            $this->addAttributeToFilter('parent_id', ['in' => $seller]);
        } elseif ($seller->getId()) {
            $this->addAttributeToFilter('parent_id', $seller->getId());
        } else {
            $this->addAttributeToFilter('parent_id', '-1');
        }
        return $this;
    }
}
