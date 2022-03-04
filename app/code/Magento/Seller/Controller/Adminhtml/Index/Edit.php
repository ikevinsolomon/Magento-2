<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Magento\Seller\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * Seller edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $sellerId = $this->initCurrentSeller();

        $sellerData = [];
        $sellerData['account'] = [];
        $sellerData['address'] = [];
        $seller = null;
        $isExistingSeller = (bool)$sellerId;
        if ($isExistingSeller) {
            try {
                $seller = $this->_sellerRepository->getById($sellerId);
                $sellerData['account'] = $this->sellerMapper->toFlatArray($seller);
                $sellerData['account'][SellerInterface::ID] = $sellerId;
                try {
                    $addresses = $seller->getAddresses();
                    foreach ($addresses as $address) {
                        $sellerData['address'][$address->getId()] = $this->addressMapper->toFlatArray($address);
                        $sellerData['address'][$address->getId()]['id'] = $address->getId();
                    }
                } catch (NoSuchEntityException $e) {
                    //do nothing
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while editing the seller.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('seller/*/index');
                return $resultRedirect;
            }
        }
        $sellerData['seller_id'] = $sellerId;
        $this->_getSession()->setSellerData($sellerData);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Seller::seller_manage');
        $this->prepareDefaultSellerTitle($resultPage);
        $resultPage->setActiveMenu('Magento_Seller::seller');
        if ($isExistingSeller) {
            $resultPage->getConfig()->getTitle()->prepend($this->_viewHelper->getSellerName($seller));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Seller'));
        }
        return $resultPage;
    }
}
