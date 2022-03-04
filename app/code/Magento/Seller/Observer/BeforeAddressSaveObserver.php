<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Observer;

use Magento\Seller\Helper\Address as HelperAddress;
use Magento\Seller\Model\Address\AbstractAddress;
use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Seller\Model\Address;

/**
 * Seller Observer Model
 */
class BeforeAddressSaveObserver implements ObserverInterface
{
    /**
     * VAT ID validation currently saved address flag
     */
    const VIV_CURRENTLY_SAVED_ADDRESS = 'currently_saved_address';

    /**
     * @var HelperAddress
     */
    protected $_sellerAddress;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param HelperAddress $sellerAddress
     * @param Registry $coreRegistry
     */
    public function __construct(
        HelperAddress $sellerAddress,
        Registry $coreRegistry
    ) {
        $this->_sellerAddress = $sellerAddress;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Address before save event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_coreRegistry->registry(self::VIV_CURRENTLY_SAVED_ADDRESS)) {
            $this->_coreRegistry->unregister(self::VIV_CURRENTLY_SAVED_ADDRESS);
        }

        /** @var $sellerAddress Address */
        $sellerAddress = $observer->getSellerAddress();
        if ($sellerAddress->getId()) {
            $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, $sellerAddress->getId());
        } else {
            $configAddressType = $this->_sellerAddress->getTaxCalculationAddressType();
            $forceProcess = $configAddressType == AbstractAddress::TYPE_SHIPPING
                ? $sellerAddress->getIsDefaultShipping()
                : $sellerAddress->getIsDefaultBilling();
            if ($forceProcess) {
                $sellerAddress->setForceProcess(true);
            } else {
                $this->_coreRegistry->register(self::VIV_CURRENTLY_SAVED_ADDRESS, 'new_address');
            }
        }
    }
}
