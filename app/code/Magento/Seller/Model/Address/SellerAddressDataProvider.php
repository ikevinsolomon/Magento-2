<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Address;

/**
 * Provides seller address data.
 */
class SellerAddressDataProvider
{
    /**
     * Seller addresses.
     *
     * @var array
     */
    private $sellerAddresses = [];

    /**
     * @var SellerAddressDataFormatter
     */
    private $sellerAddressDataFormatter;

    /**
     * @param SellerAddressDataFormatter $sellerAddressDataFormatter
     */
    public function __construct(
        SellerAddressDataFormatter $sellerAddressDataFormatter
    ) {
        $this->sellerAddressDataFormatter = $sellerAddressDataFormatter;
    }

    /**
     * Get addresses for seller.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressDataBySeller(
        \Magento\Seller\Api\Data\SellerInterface $seller
    ): array {
        if (!empty($this->sellerAddresses)) {
            return $this->sellerAddresses;
        }

        $sellerOriginAddresses = $seller->getAddresses();
        if (!$sellerOriginAddresses) {
            return [];
        }

        $sellerAddresses = [];
        foreach ($sellerOriginAddresses as $address) {
            $sellerAddresses[$address->getId()] = $this->sellerAddressDataFormatter->prepareAddress($address);
        }

        $this->sellerAddresses = $sellerAddresses;

        return $this->sellerAddresses;
    }
}
