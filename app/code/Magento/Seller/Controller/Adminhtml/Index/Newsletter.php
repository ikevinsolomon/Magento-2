<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

class Newsletter extends \Magento\Seller\Controller\Adminhtml\Index
{
    /**
     * Seller newsletter grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $sellerId = $this->initCurrentSeller();
        /** @var  \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->_objectManager
            ->create(\Magento\Newsletter\Model\Subscriber::class)
            ->loadBySellerId($sellerId);

        $this->_coreRegistry->register('subscriber', $subscriber);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
