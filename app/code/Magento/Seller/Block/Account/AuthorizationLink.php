<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account;

use Magento\Seller\Model\Context;
use Magento\Seller\Block\Account\SortLinkInterface;

/**
 * Seller authorization link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class AuthorizationLink extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * Seller session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Seller\Model\Url
     */
    protected $_sellerUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Seller\Model\Url $sellerUrl
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Seller\Model\Url $sellerUrl,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_sellerUrl = $sellerUrl;
        $this->_postDataHelper = $postDataHelper;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->isLoggedIn()
            ? $this->_sellerUrl->getLogoutUrl()
            : $this->_sellerUrl->getLoginUrl();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->isLoggedIn() ? __('Sign Out') : __('Sign In');
    }

    /**
     * Retrieve params for post request
     *
     * @return string
     */
    public function getPostParams()
    {
        return $this->_postDataHelper->getPostData($this->getHref());
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
