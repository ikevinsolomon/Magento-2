<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\Seller;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Convert\ConvertArray;

/**
 * Class Mapper converts Address Service Data Object to an array
 */
class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(ExtensibleDataObjectConverter $extensibleDataObjectConverter)
    {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert address data object to a flat array
     *
     * @param SellerInterface $seller
     * @return array
     */
    public function toFlatArray(SellerInterface $seller)
    {
        $flatArray = $this->extensibleDataObjectConverter->toNestedArray(
            $seller,
            [],
            \Magento\Seller\Api\Data\SellerInterface::class
        );
        unset($flatArray["addresses"]);
        return ConvertArray::toFlatArray($flatArray);
    }
}
