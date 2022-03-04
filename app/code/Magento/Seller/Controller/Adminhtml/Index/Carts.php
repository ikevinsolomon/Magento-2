<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

class Carts extends \Magento\Seller\Controller\Adminhtml\Index
{
    /**
     * Get shopping carts from all websites for specified client
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentSeller();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
