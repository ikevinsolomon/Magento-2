<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Address;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Form extends \Magento\Seller\Controller\Address implements HttpGetActionInterface
{
    /**
     * Address book form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('seller_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('seller/address');
        }
        return $resultPage;
    }
}
