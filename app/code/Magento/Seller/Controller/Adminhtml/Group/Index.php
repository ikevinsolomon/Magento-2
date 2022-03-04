<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Group;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Seller\Controller\Adminhtml\Group implements HttpGetActionInterface
{
    /**
     * Seller groups list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Seller::seller_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Seller Groups'));
        $resultPage->addBreadcrumb(__('Sellers'), __('Sellers'));
        $resultPage->addBreadcrumb(__('Seller Groups'), __('Seller Groups'));
        return $resultPage;
    }
}
