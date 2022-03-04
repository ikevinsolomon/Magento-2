<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Seller\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * Sellers list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Magento_Seller::seller_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Sellers'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Sellers'), __('Sellers'));
        $resultPage->addBreadcrumb(__('Manage Sellers'), __('Manage Sellers'));

        $this->_getSession()->unsSellerData();
        $this->_getSession()->unsSellerFormData();

        return $resultPage;
    }
}
