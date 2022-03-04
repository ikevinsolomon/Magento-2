<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Block\Address;

use Magento\Seller\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Seller address grid
 *
 * @api
 * @since 102.0.1
 */
class Grid extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Seller\Helper\Session\CurrentSeller
     */
    private $currentSeller;

    /**
     * @var \Magento\Seller\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Seller\Model\ResourceModel\Address\Collection
     */
    private $addressCollection;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Seller\Helper\Session\CurrentSeller $currentSeller
     * @param AddressCollectionFactory $addressCollectionFactory
     * @param CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Seller\Helper\Session\CurrentSeller $currentSeller,
        AddressCollectionFactory $addressCollectionFactory,
        CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->currentSeller = $currentSeller;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->countryFactory = $countryFactory;

        parent::__construct($context, $data);
    }

    /**
     * Prepare the Address Book section layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 102.0.1
     */
    protected function _prepareLayout(): void
    {
        parent::_prepareLayout();
        $this->preparePager();
    }

    /**
     * Generate and return "New Address" URL
     *
     * @return string
     * @since 102.0.1
     */
    public function getAddAddressUrl(): string
    {
        return $this->getUrl('seller/address/new', ['_secure' => true]);
    }

    /**
     * Generate and return "Delete" URL
     *
     * @return string
     * @since 102.0.1
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl('seller/address/delete');
    }

    /**
     * Generate and return "Edit Address" URL.
     *
     * Address ID passed in parameters
     *
     * @param int $addressId
     * @return string
     * @since 102.0.1
     */
    public function getAddressEditUrl($addressId): string
    {
        return $this->getUrl('seller/address/edit', ['_secure' => true, 'id' => $addressId]);
    }

    /**
     * Get current additional seller addresses
     *
     * Return array of address interfaces if seller has additional addresses and false in other cases
     *
     * @return \Magento\Seller\Api\Data\AddressInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     * @since 102.0.1
     */
    public function getAdditionalAddresses(): array
    {
        $additional = [];
        $addresses = $this->getAddressCollection();
        $primaryAddressIds = [$this->getDefaultBilling(), $this->getDefaultShipping()];
        foreach ($addresses as $address) {
            if (!in_array((int)$address->getId(), $primaryAddressIds, true)) {
                $additional[] = $address->getDataModel();
            }
        }
        return $additional;
    }

    /**
     * Get current seller
     *
     * Return stored seller or get it from session
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @since 102.0.1
     */
    public function getSeller(): \Magento\Seller\Api\Data\SellerInterface
    {
        $seller = $this->getData('seller');
        if ($seller === null) {
            $seller = $this->currentSeller->getSeller();
            $this->setData('seller', $seller);
        }
        return $seller;
    }

    /**
     * Get one string street address from the Address DTO passed in parameters
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @return string
     * @since 102.0.1
     */
    public function getStreetAddress(\Magento\Seller\Api\Data\AddressInterface $address): string
    {
        $street = $address->getStreet();
        if (is_array($street)) {
            $street = implode(', ', $street);
        }
        return $street;
    }

    /**
     * Get country name by $countryCode
     *
     * Using \Magento\Directory\Model\Country to get country name by $countryCode
     *
     * @param string $countryCode
     * @return string
     * @since 102.0.1
     */
    public function getCountryByCode(string $countryCode): string
    {
        /** @var \Magento\Directory\Model\Country $country */
        $country = $this->countryFactory->create();
        return $country->loadByCode($countryCode)->getName();
    }

    /**
     * Get default billing address
     *
     * Return address string if address found and null if not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultBilling(): int
    {
        $seller = $this->getSeller();

        return (int)$seller->getDefaultBilling();
    }

    /**
     * Get default shipping address
     *
     * Return address string if address found and null if not
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultShipping(): int
    {
        $seller = $this->getSeller();

        return (int)$seller->getDefaultShipping();
    }

    /**
     * Get pager layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function preparePager(): void
    {
        $addressCollection = $this->getAddressCollection();
        if (null !== $addressCollection) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'seller.addresses.pager'
            )->setCollection($addressCollection);
            $this->setChild('pager', $pager);
        }
    }

    /**
     * Get seller addresses collection.
     *
     * Filters collection by seller id
     *
     * @return \Magento\Seller\Model\ResourceModel\Address\Collection
     * @throws NoSuchEntityException
     */
    private function getAddressCollection(): \Magento\Seller\Model\ResourceModel\Address\Collection
    {
        if (null === $this->addressCollection) {
            if (null === $this->getSeller()) {
                throw new NoSuchEntityException(__('Seller not logged in'));
            }
            /** @var \Magento\Seller\Model\ResourceModel\Address\Collection $collection */
            $collection = $this->addressCollectionFactory->create();
            $collection->setOrder('entity_id', 'desc');
            $collection->addFieldToFilter(
                'entity_id',
                ['nin' => [$this->getDefaultBilling(), $this->getDefaultShipping()]]
            );
            $collection->setSellerFilter([$this->getSeller()->getId()]);
            $this->addressCollection = $collection;
        }
        return $this->addressCollection;
    }
}
