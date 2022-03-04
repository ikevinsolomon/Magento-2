<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

class Orders extends \Magento\Seller\Controller\Adminhtml\Index
{
    /**
     * Seller orders grid
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
