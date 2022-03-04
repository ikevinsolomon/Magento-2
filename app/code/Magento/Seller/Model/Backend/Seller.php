<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Backend;

class Seller extends \Magento\Seller\Model\Seller
{
    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->getWebsiteId() * 1) {
            return $this->_getWebsiteStoreId();
        }
        return parent::getStoreId();
    }
}
