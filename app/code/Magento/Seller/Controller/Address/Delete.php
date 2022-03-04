<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Address;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Delete seller address controller action.
 */
class Delete extends \Magento\Seller\Controller\Address implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @inheritdoc
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId && $this->_formKeyValidator->validate($this->getRequest())) {
            try {
                $address = $this->_addressRepository->getById($addressId);
                if ($address->getSellerId() === $this->_getSession()->getSellerId()) {
                    $this->_addressRepository->deleteById($addressId);
                    $this->messageManager->addSuccessMessage(__('You deleted the address.'));
                } else {
                    $this->messageManager->addErrorMessage(__('We can\'t delete the address right now.'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('We can\'t delete the address right now.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
