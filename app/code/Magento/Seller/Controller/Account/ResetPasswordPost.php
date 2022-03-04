<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Account;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Seller\Model\Seller\CredentialsValidator;

/**
 * Seller reset password controller
 */
class ResetPasswordPost extends \Magento\Seller\Controller\AbstractAccount implements HttpPostActionInterface
{
    /**
     * @var \Magento\Seller\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param Context $context
     * @param Session $sellerSession
     * @param AccountManagementInterface $accountManagement
     * @param SellerRepositoryInterface $sellerRepository
     * @param CredentialsValidator|null $credentialsValidator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        AccountManagementInterface $accountManagement,
        SellerRepositoryInterface $sellerRepository,
        CredentialsValidator $credentialsValidator = null
    ) {
        $this->session = $sellerSession;
        $this->accountManagement = $accountManagement;
        $this->sellerRepository = $sellerRepository;
        parent::__construct($context);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addErrorMessage(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);

            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addErrorMessage(__('Please enter a new password.'));
            $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);

            return $resultRedirect;
        }

        try {
            $this->accountManagement->resetPassword(
                null,
                $resetPasswordToken,
                $password
            );
            // logout from current session if password changed.
            if ($this->session->isLoggedIn()) {
                $this->session->logout();
                $this->session->start();
            }
            $this->session->unsRpToken();
            $this->messageManager->addSuccessMessage(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');

            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the new password.'));
        }
        $resultRedirect->setPath('*/*/createPassword', ['token' => $resetPasswordToken]);

        return $resultRedirect;
    }
}
