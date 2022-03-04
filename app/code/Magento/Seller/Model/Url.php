<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Seller url model
 */
class Url
{
    /**
     * Route for seller account login page
     */
    const ROUTE_ACCOUNT_LOGIN = 'seller/account/login';

    /**
     * Config name for Redirect Seller to Account Dashboard after Logging in setting
     */
    const XML_PATH_SELLER_STARTUP_REDIRECT_TO_DASHBOARD = 'seller/startup/redirect_dashboard';

    /**
     * Query param name for last url visited
     */
    const REFERER_QUERY_PARAM_NAME = 'referer';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $sellerSession;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var \Magento\Framework\Url\HostChecker
     */
    private $hostChecker;

    /**
     * @param Session $sellerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface|null $urlDecoder
     * @param \Magento\Framework\Url\HostChecker|null $hostChecker
     */
    public function __construct(
        Session $sellerSession,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder = null,
        \Magento\Framework\Url\HostChecker $hostChecker = null
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->sellerSession = $sellerSession;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Url\DecoderInterface::class);
        $this->hostChecker = $hostChecker ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Url\HostChecker::class);
    }

    /**
     * Retrieve seller login url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->urlBuilder->getUrl(self::ROUTE_ACCOUNT_LOGIN, $this->getLoginUrlParams());
    }

    /**
     * Retrieve parameters of seller login url
     *
     * @return array
     */
    public function getLoginUrlParams()
    {
        $params = [];
        $referer = $this->getRequestReferrer();
        if (!$referer
            && !$this->scopeConfig->isSetFlag(
                self::XML_PATH_SELLER_STARTUP_REDIRECT_TO_DASHBOARD,
                ScopeInterface::SCOPE_STORE
            )
            && !$this->sellerSession->getNoReferer()
        ) {
            $referer = $this->urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $referer = $this->urlEncoder->encode($referer);
        }

        if ($referer) {
            $params = [self::REFERER_QUERY_PARAM_NAME => $referer];
        }

        return $params;
    }

    /**
     * Retrieve seller login POST URL
     *
     * @return string
     */
    public function getLoginPostUrl()
    {
        $params = [];
        $referer = $this->getRequestReferrer();
        if ($referer) {
            $params = [
                self::REFERER_QUERY_PARAM_NAME => $referer,
            ];
        }
        return $this->urlBuilder->getUrl('seller/account/loginPost', $params);
    }

    /**
     * Retrieve seller logout url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->urlBuilder->getUrl('seller/account/logout');
    }

    /**
     * Retrieve seller dashboard url
     *
     * @return string
     */
    public function getDashboardUrl()
    {
        return $this->urlBuilder->getUrl('seller/account');
    }

    /**
     * Retrieve seller account page url
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->urlBuilder->getUrl('seller/account');
    }

    /**
     * Retrieve seller register form url
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->urlBuilder->getUrl('seller/account/create');
    }

    /**
     * Retrieve seller register form post url
     *
     * @return string
     */
    public function getRegisterPostUrl()
    {
        return $this->urlBuilder->getUrl('seller/account/createpost');
    }

    /**
     * Retrieve seller account edit form url
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->urlBuilder->getUrl('seller/account/edit');
    }

    /**
     * Retrieve seller edit POST URL
     *
     * @return string
     */
    public function getEditPostUrl()
    {
        return $this->urlBuilder->getUrl('seller/account/editpost');
    }

    /**
     * Retrieve url of forgot password page
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->urlBuilder->getUrl('seller/account/forgotpassword');
    }

    /**
     * Retrieve confirmation URL for Email
     *
     * @param string $email
     * @return string
     */
    public function getEmailConfirmationUrl($email = null)
    {
        return $this->urlBuilder->getUrl('seller/account/confirmation', ['_query' => ['email' => $email]]);
    }

    /**
     * @return mixed|null
     */
    private function getRequestReferrer()
    {
        $referer = $this->request->getParam(self::REFERER_QUERY_PARAM_NAME);
        if ($referer && $this->hostChecker->isOwnOrigin($this->urlDecoder->decode($referer))) {
            return $referer;
        }
        return null;
    }
}
