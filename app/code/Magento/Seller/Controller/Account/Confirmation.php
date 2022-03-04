<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Account;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Controller\AbstractAccount;
use Magento\Seller\Model\Session;
use Magento\Seller\Model\Url;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Confirmation. Send confirmation link to specified email
 */
class Confirmation extends AbstractAccount implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Seller\Api\AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Url
     */
    private $sellerUrl;

    /**
     * @param Context $context
     * @param Session $sellerSession
     * @param PageFactory $resultPageFactory
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $sellerAccountManagement
     * @param Url $sellerUrl
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $sellerAccountManagement,
        Url $sellerUrl = null
    ) {
        $this->session = $sellerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->sellerAccountManagement = $sellerAccountManagement;
        $this->sellerUrl = $sellerUrl ?: ObjectManager::getInstance()->get(Url::class);
        parent::__construct($context);
    }

    /**
     * Send confirmation link to specified email
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        // try to confirm by email
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            try {
                $this->sellerAccountManagement->resendConfirmation(
                    $email,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $this->messageManager->addSuccessMessage(__('Please check your email for confirmation key.'));
            } catch (InvalidTransitionException $e) {
                $this->messageManager->addSuccessMessage(__('This email does not require confirmation.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Wrong email.'));
                $resultRedirect->setPath('*/*/*', ['email' => $email, '_secure' => true]);
                return $resultRedirect;
            }
            $this->session->setUsername($email);
            $resultRedirect->setPath('*/*/index', ['_secure' => true]);
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('accountConfirmation')->setEmail(
            $this->getRequest()->getParam('email', $email)
        )->setLoginUrl(
            $this->sellerUrl->getLoginUrl()
        );
        return $resultPage;
    }
}
