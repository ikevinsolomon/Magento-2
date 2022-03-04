<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Observer;

use Magento\Seller\Model\AuthenticationInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SellerLoginSuccessObserver
 */
class SellerLoginSuccessObserver implements ObserverInterface
{
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        AuthenticationInterface $authentication
    ) {
        $this->authentication = $authentication;
    }

    /**
     * Unlock seller on success login attempt.
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Seller\Model\Seller $seller */
        $seller = $observer->getEvent()->getData('model');
        $this->authentication->unlock($seller->getId());
        return $this;
    }
}
