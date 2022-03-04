<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Delegation;

use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\Data\RegionInterface;
use Magento\Seller\Api\Data\RegionInterfaceFactory;
use Magento\Seller\Model\Delegation\Data\NewOperation;
use Magento\Seller\Model\Data\Seller;
use Magento\Seller\Model\Data\Address;
use Magento\Seller\Model\Session;
use Magento\Seller\Model\Session\Proxy as SessionProxy;
use Magento\Seller\Model\Delegation\Data\NewOperationFactory;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Psr\Log\LoggerInterface;

/**
 * Store data for delegated operations.
 */
class Storage
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var NewOperationFactory
     */
    private $newFactory;

    /**
     * @var SellerInterfaceFactory
     */
    private $sellerFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param NewOperationFactory $newFactory
     * @param SellerInterfaceFactory $sellerFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param RegionInterfaceFactory $regionFactory
     * @param LoggerInterface $logger
     * @param SessionProxy $session
     */
    public function __construct(
        NewOperationFactory $newFactory,
        SellerInterfaceFactory $sellerFactory,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        LoggerInterface $logger,
        SessionProxy $session
    ) {
        $this->newFactory = $newFactory;
        $this->sellerFactory = $sellerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * Store data for new account operation.
     *
     * @param SellerInterface $seller
     * @param array $delegatedData
     *
     * @return void
     */
    public function storeNewOperation(SellerInterface $seller, array $delegatedData): void
    {
        /** @var Seller $seller */
        $sellerData = $seller->__toArray();
        $addressesData = [];
        if ($seller->getAddresses()) {
            /** @var Address $address */
            foreach ($seller->getAddresses() as $address) {
                $addressesData[] = $address->__toArray();
            }
        }
        $this->session->setSellerFormData($sellerData);
        $this->session->setDelegatedNewSellerData(
            [
                'seller' => $sellerData,
                'addresses' => $addressesData,
                'delegated_data' => $delegatedData,
            ]
        );
    }

    /**
     * Retrieve delegated new operation data and mark it as used.
     *
     * @return NewOperation|null
     */
    public function consumeNewOperation()
    {
        try {
            $serialized = $this->session->getDelegatedNewSellerData(true);
        } catch (\Throwable $exception) {
            $this->logger->error($exception);
            $serialized = null;
        }
        if ($serialized === null) {
            return null;
        }

        /** @var AddressInterface[] $addresses */
        $addresses = [];
        foreach ($serialized['addresses'] as $addressData) {
            if (isset($addressData['region'])) {
                /** @var RegionInterface $region */
                $region = $this->regionFactory->create(
                    ['data' => $addressData['region']]
                );
                $addressData['region'] = $region;
            }

            $customAttributes = [];
            if (!empty($addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
                $customAttributes = $addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES];
                unset($addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES]);
            }

            $address = $this->addressFactory->create(
                ['data' => $addressData]
            );

            foreach ($customAttributes as $attributeCode => $attributeValue) {
                $address->setCustomAttribute($attributeCode, $attributeValue);
            }

            $addresses[] = $address;
        }
        $sellerData = $serialized['seller'];
        $sellerData['addresses'] = $addresses;

        return $this->newFactory->create(
            [
                'seller' => $this->sellerFactory->create(['data' => $sellerData]),
                'additionalData' => $serialized['delegated_data'],
            ]
        );
    }
}
