<?php
/**
 * Seller address entity resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel;

use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Model\Address as SellerAddressModel;
use Magento\Seller\Model\Seller as SellerModel;
use Magento\Seller\Model\ResourceModel\Address\Collection;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;

/**
 * Address repository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepository implements \Magento\Seller\Api\AddressRepositoryInterface
{
    /**
     * Directory data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;

    /**
     * @var \Magento\Seller\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Seller\Model\AddressRegistry
     */
    protected $addressRegistry;

    /**
     * @var \Magento\Seller\Model\SellerRegistry
     */
    protected $sellerRegistry;

    /**
     * @var \Magento\Seller\Model\ResourceModel\Address
     */
    protected $addressResourceModel;

    /**
     * @var \Magento\Seller\Api\Data\AddressSearchResultsInterfaceFactory
     */
    protected $addressSearchResultsFactory;

    /**
     * @var \Magento\Seller\Model\ResourceModel\Address\CollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param \Magento\Seller\Model\AddressFactory $addressFactory
     * @param \Magento\Seller\Model\AddressRegistry $addressRegistry
     * @param \Magento\Seller\Model\SellerRegistry $sellerRegistry
     * @param \Magento\Seller\Model\ResourceModel\Address $addressResourceModel
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Seller\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory
     * @param \Magento\Seller\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Magento\Seller\Model\AddressFactory $addressFactory,
        \Magento\Seller\Model\AddressRegistry $addressRegistry,
        \Magento\Seller\Model\SellerRegistry $sellerRegistry,
        \Magento\Seller\Model\ResourceModel\Address $addressResourceModel,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Seller\Api\Data\AddressSearchResultsInterfaceFactory $addressSearchResultsFactory,
        \Magento\Seller\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRegistry = $addressRegistry;
        $this->sellerRegistry = $sellerRegistry;
        $this->addressResourceModel = $addressResourceModel;
        $this->directoryData = $directoryData;
        $this->addressSearchResultsFactory = $addressSearchResultsFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save seller address.
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @return \Magento\Seller\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Seller\Api\Data\AddressInterface $address)
    {
        $addressModel = null;
        $sellerModel = $this->sellerRegistry->retrieve($address->getSellerId());
        if ($address->getId()) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
        }

        if ($addressModel === null) {
            /** @var \Magento\Seller\Model\Address $addressModel */
            $addressModel = $this->addressFactory->create();
            $addressModel->updateData($address);
            $addressModel->setSeller($sellerModel);
        } else {
            $addressModel->updateData($address);
        }
        $addressModel->setStoreId($sellerModel->getStoreId());

        $errors = $addressModel->validate();
        if ($errors !== true) {
            $inputException = new InputException();
            foreach ($errors as $error) {
                $inputException->addError($error);
            }
            throw $inputException;
        }
        $addressModel->save();
        $address->setId($addressModel->getId());
        // Clean up the seller registry since the Address save has a
        // side effect on seller : \Magento\Seller\Model\ResourceModel\Address::_afterSave
        $this->addressRegistry->push($addressModel);
        $this->updateAddressCollection($sellerModel, $addressModel);

        return $addressModel->getDataModel();
    }

    /**
     * Update address collection.
     *
     * @param Seller $seller
     * @param Address $address
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function updateAddressCollection(SellerModel $seller, SellerAddressModel $address)
    {
        $seller->getAddressesCollection()->removeItemByKey($address->getId());
        $seller->getAddressesCollection()->addItem($address);
    }

    /**
     * Retrieve seller address.
     *
     * @param int $addressId
     * @return \Magento\Seller\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        return $address->getDataModel();
    }

    /**
     * Retrieve sellers addresses matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Seller\Api\Data\AddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->addressCollectionFactory->create();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Magento\Seller\Api\Data\AddressInterface::class
        );

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Seller\Api\Data\AddressInterface[] $addresses */
        $addresses = [];
        /** @var \Magento\Seller\Model\Address $address */
        foreach ($collection->getItems() as $address) {
            $addresses[] = $this->getById($address->getId());
        }

        /** @var \Magento\Seller\Api\Data\AddressSearchResultsInterface $searchResults */
        $searchResults = $this->addressSearchResultsFactory->create();
        $searchResults->setItems($addresses);
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 101.0.0
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Delete seller address.
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Seller\Api\Data\AddressInterface $address)
    {
        $addressId = $address->getId();
        $address = $this->addressRegistry->retrieve($addressId);
        $sellerModel = $this->sellerRegistry->retrieve($address->getSellerId());
        $sellerModel->getAddressesCollection()->clear();
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Delete seller address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId)
    {
        $address = $this->addressRegistry->retrieve($addressId);
        $sellerModel = $this->sellerRegistry->retrieve($address->getSellerId());
        $sellerModel->getAddressesCollection()->removeItemByKey($addressId);
        $this->addressResourceModel->delete($address);
        $this->addressRegistry->remove($addressId);
        return true;
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}
