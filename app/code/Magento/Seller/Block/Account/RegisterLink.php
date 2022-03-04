<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account;

use Magento\Seller\Model\Context;

/**
 * Seller register link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class RegisterLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Seller session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Seller\Model\Registration
     */
    protected $_registration;

    /**
     * @var \Magento\Seller\Model\Url
     */
    protected $_sellerUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Seller\Model\Registration $registration
     * @param \Magento\Seller\Model\Url $sellerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Seller\Model\Registration $registration,
        \Magento\Seller\Model\Url $sellerUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_registration = $registration;
        $this->_sellerUrl = $sellerUrl;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_sellerUrl->getRegisterUrl();
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->_registration->isAllowed()
            || $this->httpContext->getValue(Context::CONTEXT_AUTH)
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
