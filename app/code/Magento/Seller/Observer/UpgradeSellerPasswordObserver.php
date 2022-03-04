<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Observer;

use Magento\Seller\Model\Seller;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\SellerRegistry;

/**
 * Class observer UpgradeSellerPasswordObserver to upgrade seller password hash when seller has logged in
 */
class UpgradeSellerPasswordObserver implements ObserverInterface
{
    /**
     * Encryption model
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var SellerRegistry
     */
    private $sellerRegistry;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @param EncryptorInterface $encryptor
     * @param SellerRegistry $sellerRegistry
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(
        EncryptorInterface $encryptor,
        SellerRegistry $sellerRegistry,
        SellerRepositoryInterface $sellerRepository
    ) {
        $this->encryptor = $encryptor;
        $this->sellerRegistry = $sellerRegistry;
        $this->sellerRepository = $sellerRepository;
    }

    /**
     * Upgrade seller password hash when seller has logged in
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $password = $observer->getEvent()->getData('password');
        /** @var \Magento\Seller\Model\Seller $model */
        $model = $observer->getEvent()->getData('model');
        $seller = $this->sellerRepository->getById($model->getId());
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($model->getId());

        if (!$this->encryptor->validateHashVersion($sellerSecure->getPasswordHash(), true)) {
            $sellerSecure->setPasswordHash($this->encryptor->getHash($password, true));
            // No need to validate seller and seller address while upgrading seller password
            $this->setIgnoreValidationFlag($seller);
            $this->sellerRepository->save($seller);
        }
    }

    /**
     * Set ignore_validation_flag to skip unnecessary address and seller validation
     *
     * @param Seller $seller
     * @return void
     */
    private function setIgnoreValidationFlag($seller)
    {
        $seller->setData('ignore_validation_flag', true);
    }
}
