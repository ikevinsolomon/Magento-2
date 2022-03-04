<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Address\Validator;

use Magento\Seller\Model\Address\AbstractAddress;
use Magento\Seller\Model\Address\ValidatorInterface;
use Magento\Seller\Model\AddressFactory;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;

/**
 * Validates that current Address is related to given Seller.
 */
class Seller implements ValidatorInterface
{
    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param AddressFactory $addressFactory
     */
    public function __construct(AddressFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractAddress $address): array
    {
        $errors = [];
        $addressId = $address instanceof QuoteAddressInterface ? $address->getSellerAddressId() : $address->getId();
        if ($addressId !== null) {
            $addressSellerId = (int) $address->getSellerId();
            $originalAddressSellerId = (int) $this->addressFactory->create()
                ->load($addressId)
                ->getSellerId();

            if ($originalAddressSellerId !== 0 && $originalAddressSellerId !== $addressSellerId) {
                $errors[] = __(
                    'Provided seller ID "%seller_id" isn\'t related to current seller address.',
                    ['seller_id' => $addressSellerId]
                );
            }
        }

        return $errors;
    }
}
