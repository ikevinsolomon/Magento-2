<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Exception;

class Configure extends \Magento\Seller\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist
{
    /**
     * Ajax handler to response configuration fieldset of composite product in seller's wishlist.
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $configureResult = new \Magento\Framework\DataObject();
        try {
            $this->_initData();

            $configureResult->setProductId($this->_wishlistItem->getProductId());
            $configureResult->setBuyRequest($this->_wishlistItem->getBuyRequest());
            $configureResult->setCurrentStoreId($this->_wishlistItem->getStoreId());
            $configureResult->setCurrentSellerId($this->_wishlist->getSellerId());

            $configureResult->setOk(true);
        } catch (Exception $e) {
            $configureResult->setError(true);
            $configureResult->setMessage($e->getMessage());
        }

        return $this->_objectManager->get(\Magento\Catalog\Helper\Product\Composite::class)
            ->renderConfigureResult($configureResult);
    }
}
