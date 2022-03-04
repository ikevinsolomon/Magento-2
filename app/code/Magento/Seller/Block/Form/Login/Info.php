<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Form\Login;

/**
 * Seller login info block
 *
 * @api
 * @since 100.0.2
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Seller\Model\Url
     */
    protected $_sellerUrl;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;

    /**
     * Registration
     *
     * @var \Magento\Seller\Model\Registration
     */
    protected $registration;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Seller\Model\Registration $registration
     * @param \Magento\Seller\Model\Url $sellerUrl
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Seller\Model\Registration $registration,
        \Magento\Seller\Model\Url $sellerUrl,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registration = $registration;
        $this->_sellerUrl = $sellerUrl;
        $this->checkoutData = $checkoutData;
        $this->coreUrl = $coreUrl;
    }

    /**
     * Return registration
     *
     * @return \Magento\Seller\Model\Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Retrieve create new account url
     *
     * @return string
     */
    public function getCreateAccountUrl()
    {
        $url = $this->getData('create_account_url');
        if ($url === null) {
            $url = $this->_sellerUrl->getRegisterUrl();
        }
        if ($this->checkoutData->isContextCheckout()) {
            $url = $this->coreUrl->addRequestParam($url, ['context' => 'checkout']);
        }
        return $url;
    }
}
