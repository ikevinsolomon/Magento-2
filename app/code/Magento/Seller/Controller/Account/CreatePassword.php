<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Controller\Account;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Model\ForgotPasswordToken\ConfirmSellerByToken;
use Magento\Seller\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;

/**
 * Class CreatePassword
 *
 * @package Magento\Seller\Controller\Account
 */
class CreatePassword extends \Magento\Seller\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Seller\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Seller\Model\ForgotPasswordToken\ConfirmSellerByToken
     */
    private $confirmByToken;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Seller\Model\Session $sellerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Seller\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Seller\Model\ForgotPasswordToken\ConfirmSellerByToken $confirmByToken
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        ConfirmSellerByToken $confirmByToken = null
    ) {
        $this->session = $sellerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->confirmByToken = $confirmByToken
            ?? ObjectManager::getInstance()->get(ConfirmSellerByToken::class);

        parent::__construct($context);
    }

    /**
     * Resetting password handler
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resetPasswordToken = (string)$this->getRequest()->getParam('token');
        $isDirectLink = $resetPasswordToken != '';
        if (!$isDirectLink) {
            $resetPasswordToken = (string)$this->session->getRpToken();
        }

        try {
            $this->accountManagement->validateResetPasswordLinkToken(null, $resetPasswordToken);

            $this->confirmByToken->execute($resetPasswordToken);

            if ($isDirectLink) {
                $this->session->setRpToken($resetPasswordToken);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/createpassword');

                return $resultRedirect;
            } else {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getLayout()
                           ->getBlock('resetPassword')
                           ->setResetPasswordLinkToken($resetPasswordToken);

                return $resultPage;
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/forgotpassword');

            return $resultRedirect;
        }
    }
}
