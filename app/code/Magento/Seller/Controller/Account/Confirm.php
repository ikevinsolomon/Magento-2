<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Account;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Controller\AbstractAccount;
use Magento\Seller\Helper\Address;
use Magento\Seller\Model\Session;
use Magento\Seller\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Confirm
 *
 * Confirm class is responsible for account confirmation flow
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Confirm extends AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Seller\Api\AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var \Magento\Seller\Helper\Address
     */
    protected $addressHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @param Context $context
     * @param Session $sellerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $sellerAccountManagement
     * @param SellerRepositoryInterface $sellerRepository
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $sellerAccountManagement,
        SellerRepositoryInterface $sellerRepository,
        Address $addressHelper,
        UrlFactory $urlFactory
    ) {
        $this->session = $sellerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->sellerAccountManagement = $sellerAccountManagement;
        $this->sellerRepository = $sellerRepository;
        $this->addressHelper = $addressHelper;
        $this->urlModel = $urlFactory->create();
        parent::__construct($context);
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 101.0.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 101.0.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Confirm seller account by id and confirmation key
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->session->isLoggedIn()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $sellerId = $this->getRequest()->getParam('id', false);
        $key = $this->getRequest()->getParam('key', false);
        if (empty($sellerId) || empty($key)) {
            $this->messageManager->addErrorMessage(__('Bad request.'));
            $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
            return $resultRedirect->setUrl($this->_redirect->error($url));
        }

        try {
            // log in and send greeting email
            $sellerEmail = $this->sellerRepository->getById($sellerId)->getEmail();
            $seller = $this->sellerAccountManagement->activate($sellerEmail, $key);
            $this->session->setSellerDataAsLoggedIn($seller);
            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }
            $this->messageManager->addSuccess($this->getSuccessMessage());
            $resultRedirect->setUrl($this->getSuccessRedirect());
            return $resultRedirect;
        } catch (StateException $e) {
            $this->messageManager->addException($e, __('This confirmation key is invalid or has expired.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error confirming the account'));
        }

        $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        return $resultRedirect->setUrl($this->_redirect->error($url));
    }

    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT seller, please click <a href="%1">here</a> to enter your shipping address for proper VAT calculation.',
                    $this->urlModel->getUrl('seller/address/edit')
                );
                // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT seller, please click <a href="%1">here</a> to enter your billing address for proper VAT calculation.',
                    $this->urlModel->getUrl('seller/address/edit')
                );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $message = __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
        }
        return $message;
    }

    /**
     * Retrieve success redirect URL
     *
     * @return string
     */
    protected function getSuccessRedirect()
    {
        $backUrl = $this->getRequest()->getParam('back_url', false);
        $redirectToDashboard = $this->scopeConfig->isSetFlag(
            Url::XML_PATH_SELLER_STARTUP_REDIRECT_TO_DASHBOARD,
            ScopeInterface::SCOPE_STORE
        );
        if (!$redirectToDashboard && $this->session->getBeforeAuthUrl()) {
            $successUrl = $this->session->getBeforeAuthUrl(true);
        } else {
            $successUrl = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        }
        return $this->_redirect->success($backUrl ? $backUrl : $successUrl);
    }
}
