<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Helper\Session;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\Data\AddressInterface;

/**
 * Class CurrentSellerAddress
 */
class CurrentSellerAddress
{
    /**
     * @var \Magento\Seller\Helper\Session\CurrentSeller
     */
    protected $currentSeller;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @param CurrentSeller $currentSeller
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        CurrentSeller $currentSeller,
        AccountManagementInterface $accountManagement
    ) {
        $this->currentSeller = $currentSeller;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Returns default billing address form current seller
     *
     * @return AddressInterface|null
     */
    public function getDefaultBillingAddress()
    {
        return $this->accountManagement->getDefaultBillingAddress($this->currentSeller->getSellerId());
    }

    /**
     * Returns default shipping address for current seller
     *
     * @return AddressInterface|null
     */
    public function getDefaultShippingAddress()
    {
        return $this->accountManagement->getDefaultShippingAddress(
            $this->currentSeller->getSellerId()
        );
    }
}
