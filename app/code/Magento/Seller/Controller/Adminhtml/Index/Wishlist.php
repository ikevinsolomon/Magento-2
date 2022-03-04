<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

class Wishlist extends \Magento\Seller\Controller\Adminhtml\Index
{
    /**
     * Wishlist Action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $sellerId = $this->initCurrentSeller();
        $itemId = (int)$this->getRequest()->getParam('delete');
        if ($sellerId && $itemId) {
            try {
                $this->_objectManager->create(\Magento\Wishlist\Model\Item::class)->load($itemId)->delete();
            } catch (\Exception $exception) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($exception);
            }
        }

        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
