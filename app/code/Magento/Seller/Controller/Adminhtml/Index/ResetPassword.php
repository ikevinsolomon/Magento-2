<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

/**
 * Reset password controller
 *
 * @package Magento\Seller\Controller\Adminhtml\Index
 */
class ResetPassword extends \Magento\Seller\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::reset_password';

    /**
     * Reset password handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $sellerId = (int)$this->getRequest()->getParam('seller_id', 0);
        if (!$sellerId) {
            $resultRedirect->setPath('seller/index');
            return $resultRedirect;
        }

        try {
            $seller = $this->_sellerRepository->getById($sellerId);
            $this->sellerAccountManagement->initiatePasswordReset(
                $seller->getEmail(),
                \Magento\Seller\Model\AccountManagement::EMAIL_REMINDER,
                $seller->getWebsiteId()
            );
            $this->messageManager->addSuccessMessage(
                __('The seller will receive an email with a link to reset password.')
            );
        } catch (NoSuchEntityException $exception) {
            $resultRedirect->setPath('seller/index');
            return $resultRedirect;
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $messages = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            if (!count($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
        } catch (SecurityViolationException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while resetting seller password.')
            );
        }
        $resultRedirect->setPath(
            'seller/*/edit',
            ['id' => $sellerId, '_current' => true]
        );
        return $resultRedirect;
    }
}
