<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Address;

use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Model\Address\Mapper as AddressMapper;
use Magento\Seller\Model\Address\Config as AddressConfig;

/**
 * Provides method to format seller address data.
 */
class SellerAddressDataFormatter
{
    /**
     * @var AddressMapper
     */
    private $addressMapper;

    /**
     * @var AddressConfig
     */
    private $addressConfig;

    /**
     * @var CustomAttributesProcessor
     */
    private $customAttributesProcessor;

    /**
     * @param Mapper $addressMapper
     * @param Config $addressConfig
     * @param CustomAttributesProcessor $customAttributesProcessor
     */
    public function __construct(
        AddressMapper $addressMapper,
        AddressConfig $addressConfig,
        CustomAttributesProcessor $customAttributesProcessor
    ) {
        $this->addressMapper = $addressMapper;
        $this->addressConfig = $addressConfig;
        $this->customAttributesProcessor = $customAttributesProcessor;
    }

    /**
     * Prepare seller address data.
     *
     * @param AddressInterface $sellerAddress
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareAddress(AddressInterface $sellerAddress)
    {
        $resultAddress = [
            'id' => $sellerAddress->getId(),
            'seller_id' => $sellerAddress->getSellerId(),
            'company' => $sellerAddress->getCompany(),
            'prefix' => $sellerAddress->getPrefix(),
            'firstname' => $sellerAddress->getFirstname(),
            'lastname' => $sellerAddress->getLastname(),
            'middlename' => $sellerAddress->getMiddlename(),
            'suffix' => $sellerAddress->getSuffix(),
            'street' => $sellerAddress->getStreet(),
            'city' => $sellerAddress->getCity(),
            'region' => [
                'region' => $sellerAddress->getRegion()->getRegion(),
                'region_code' => $sellerAddress->getRegion()->getRegionCode(),
                'region_id' => $sellerAddress->getRegion()->getRegionId(),
            ],
            'region_id' => $sellerAddress->getRegionId(),
            'postcode' => $sellerAddress->getPostcode(),
            'country_id' => $sellerAddress->getCountryId(),
            'telephone' => $sellerAddress->getTelephone(),
            'fax' => $sellerAddress->getFax(),
            'default_billing' => $sellerAddress->isDefaultBilling(),
            'default_shipping' => $sellerAddress->isDefaultShipping(),
            'inline' => $this->getSellerAddressInline($sellerAddress),
            'custom_attributes' => [],
            'extension_attributes' => $sellerAddress->getExtensionAttributes(),
            'vat_id' => $sellerAddress->getVatId()
        ];

        if ($sellerAddress->getCustomAttributes()) {
            $sellerAddress = $sellerAddress->__toArray();
            $resultAddress['custom_attributes'] = $this->customAttributesProcessor->filterNotVisibleAttributes(
                $sellerAddress['custom_attributes']
            );
        }

        return $resultAddress;
    }

    /**
     * Set additional seller address data
     *
     * @param AddressInterface $address
     * @return string
     */
    private function getSellerAddressInline(AddressInterface $address): string
    {
        $builtOutputAddressData = $this->addressMapper->toFlatArray($address);
        return $this->addressConfig
            ->getFormatByCode(AddressConfig::DEFAULT_ADDRESS_FORMAT)
            ->getRenderer()
            ->renderArray($builtOutputAddressData);
    }
}
