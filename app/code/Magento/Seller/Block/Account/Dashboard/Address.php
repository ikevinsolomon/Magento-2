<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account\Dashboard;

use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to manage seller dashboard addresses section
 *
 * @api
 * @since 100.0.2
 */
class Address extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Seller\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var \Magento\Seller\Helper\Session\CurrentSeller
     */
    protected $currentSeller;

    /**
     * @var \Magento\Seller\Helper\Session\CurrentSellerAddress
     */
    protected $currentSellerAddress;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Seller\Helper\Session\CurrentSeller $currentSeller
     * @param \Magento\Seller\Helper\Session\CurrentSellerAddress $currentSellerAddress
     * @param \Magento\Seller\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Seller\Helper\Session\CurrentSeller $currentSeller,
        \Magento\Seller\Helper\Session\CurrentSellerAddress $currentSellerAddress,
        \Magento\Seller\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = []
    ) {
        $this->currentSeller = $currentSeller;
        $this->currentSellerAddress = $currentSellerAddress;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
        $this->addressMapper = $addressMapper;
    }

    /**
     * Get the logged in seller
     *
     * @return \Magento\Seller\Api\Data\SellerInterface|null
     */
    public function getSeller()
    {
        try {
            return $this->currentSeller->getSeller();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * HTML for Shipping Address
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getPrimaryShippingAddressHtml()
    {
        try {
            $address = $this->currentSellerAddress->getDefaultShippingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default shipping address.');
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return __('You have not set a default shipping address.');
        }
    }

    /**
     * HTML for Billing Address
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getPrimaryBillingAddressHtml()
    {
        try {
            $address = $this->currentSellerAddress->getDefaultBillingAddress();
        } catch (NoSuchEntityException $e) {
            return $this->escapeHtml(__('You have not set a default billing address.'));
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return $this->escapeHtml(__('You have not set a default billing address.'));
        }
    }

    /**
     * Get Primary Shipping Address Edit Url
     *
     * @return string
     */
    public function getPrimaryShippingAddressEditUrl()
    {
        if (!$this->getSeller()) {
            return '';
        } else {
            $address = $this->currentSellerAddress->getDefaultShippingAddress();
            $addressId = $address ? $address->getId() : null;
            return $this->_urlBuilder->getUrl(
                'seller/address/edit',
                ['id' => $addressId]
            );
        }
    }

    /**
     * Get Primary Billing Address Edit Url
     *
     * @return string
     */
    public function getPrimaryBillingAddressEditUrl()
    {
        if (!$this->getSeller()) {
            return '';
        } else {
            $address = $this->currentSellerAddress->getDefaultBillingAddress();
            $addressId = $address ? $address->getId() : null;
            return $this->_urlBuilder->getUrl(
                'seller/address/edit',
                ['id' => $addressId]
            );
        }
    }

    /**
     * Get Address Book Url
     *
     * @return string
     */
    public function getAddressBookUrl()
    {
        return $this->getUrl('seller/address/');
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param AddressInterface $address
     * @return string
     */
    protected function _getAddressHtml($address)
    {
        /** @var \Magento\Seller\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }
}
