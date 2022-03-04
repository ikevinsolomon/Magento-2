<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Seller\Model\Url;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Seller\Model\Form;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides some configurations for seller.
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     * @deprecated 101.0.4
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Url
     */
    private $sellerUrl;

    /**
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Url|null $sellerUrl
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Url $sellerUrl = null
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->sellerUrl = $sellerUrl ?? ObjectManager::getInstance()
                ->get(Url::class);
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'sellerLoginUrl' => $this->getLoginUrl(),
            'isRedirectRequired' => $this->isRedirectRequired(),
            'autocomplete' => $this->isAutocompleteEnabled(),
        ];
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function isAutocompleteEnabled()
    {
        return $this->scopeConfig->getValue(
            Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            ScopeInterface::SCOPE_STORE
        ) ? 'on' : 'off';
    }

    /**
     * Returns URL to login controller action
     *
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->sellerUrl->getLoginUrl();
    }

    /**
     * Whether redirect to login page is required
     *
     * @return bool
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isRedirectRequired()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if (strpos($this->getLoginUrl(), (string) $baseUrl) !== false) {
            return false;
        }

        return true;
    }
}
