<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\ResourceModel;

use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\Data\SellerSearchResultsInterfaceFactory;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Seller\Model\Seller as SellerModel;
use Magento\Seller\Model\Seller\NotificationStorage;
use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\SellerRegistry;
use Magento\Seller\Model\Data\SellerSecureFactory;
use Magento\Seller\Model\Delegation\Data\NewOperation;
use Magento\Seller\Model\Delegation\Storage as DelegatedStorage;
use Magento\Seller\Model\ResourceModel\Seller\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Seller repository.
 *
 * CRUD operations for seller entity
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SellerRepository implements SellerRepositoryInterface
{
    /**
     * @var SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var SellerSecureFactory
     */
    protected $sellerSecureFactory;

    /**
     * @var SellerRegistry
     */
    protected $sellerRegistry;

    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /**
     * @var Seller
     */
    protected $sellerResourceModel;

    /**
     * @var SellerMetadataInterface
     */
    protected $sellerMetadata;

    /**
     * @var SellerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var DelegatedStorage
     */
    private $delegatedStorage;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param SellerFactory $sellerFactory
     * @param SellerSecureFactory $sellerSecureFactory
     * @param SellerRegistry $sellerRegistry
     * @param AddressRepository $addressRepository
     * @param Seller $sellerResourceModel
     * @param SellerMetadataInterface $sellerMetadata
     * @param SellerSearchResultsInterfaceFactory $searchResultsFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DataObjectHelper $dataObjectHelper
     * @param ImageProcessorInterface $imageProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param NotificationStorage $notificationStorage
     * @param DelegatedStorage|null $delegatedStorage
     * @param GroupRepositoryInterface|null $groupRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SellerFactory $sellerFactory,
        SellerSecureFactory $sellerSecureFactory,
        SellerRegistry $sellerRegistry,
        AddressRepository $addressRepository,
        Seller $sellerResourceModel,
        SellerMetadataInterface $sellerMetadata,
        SellerSearchResultsInterfaceFactory $searchResultsFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DataObjectHelper $dataObjectHelper,
        ImageProcessorInterface $imageProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor,
        NotificationStorage $notificationStorage,
        DelegatedStorage $delegatedStorage = null,
        ?GroupRepositoryInterface $groupRepository = null
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->sellerSecureFactory = $sellerSecureFactory;
        $this->sellerRegistry = $sellerRegistry;
        $this->addressRepository = $addressRepository;
        $this->sellerResourceModel = $sellerResourceModel;
        $this->sellerMetadata = $sellerMetadata;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->imageProcessor = $imageProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->notificationStorage = $notificationStorage;
        $this->delegatedStorage = $delegatedStorage ?? ObjectManager::getInstance()->get(DelegatedStorage::class);
        $this->groupRepository = $groupRepository ?: ObjectManager::getInstance()->get(GroupRepositoryInterface::class);
    }

    /**
     * Create or update a seller.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @param string $passwordHash
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(SellerInterface $seller, $passwordHash = null)
    {
        /** @var NewOperation|null $delegatedNewOperation */
        $delegatedNewOperation = !$seller->getId() ? $this->delegatedStorage->consumeNewOperation() : null;
        $prevSellerData = $prevSellerDataArr = null;
        if ($seller->getId()) {
            $prevSellerData = $this->getById($seller->getId());
            $prevSellerDataArr = $prevSellerData->__toArray();
        }
        /** @var $seller \Magento\Seller\Model\Data\Seller */
        $sellerArr = $seller->__toArray();
        $seller = $this->imageProcessor->save(
            $seller,
            SellerMetadataInterface::ENTITY_TYPE_SELLER,
            $prevSellerData
        );
        $origAddresses = $seller->getAddresses();
        $seller->setAddresses([]);
        $sellerData = $this->extensibleDataObjectConverter->toNestedArray($seller, [], SellerInterface::class);
        $seller->setAddresses($origAddresses);
        /** @var SellerModel $sellerModel */
        $sellerModel = $this->sellerFactory->create(['data' => $sellerData]);
        //Model's actual ID field maybe different than "id" so "id" field from $sellerData may be ignored.
        $sellerModel->setId($seller->getId());
        $storeId = $sellerModel->getStoreId();
        if ($storeId === null) {
            $sellerModel->setStoreId(
                $prevSellerData ? $prevSellerData->getStoreId() : $this->storeManager->getStore()->getId()
            );
        }
        $this->validateGroupId($seller->getGroupId());
        $this->setSellerGroupId($sellerModel, $sellerArr, $prevSellerDataArr);
        // Need to use attribute set or future updates can cause data loss
        if (!$sellerModel->getAttributeSetId()) {
            $sellerModel->setAttributeSetId(SellerMetadataInterface::ATTRIBUTE_SET_ID_SELLER);
        }
        $this->populateSellerWithSecureData($sellerModel, $passwordHash);
        // If seller email was changed, reset RpToken info
        if ($prevSellerData && $prevSellerData->getEmail() !== $sellerModel->getEmail()) {
            $sellerModel->setRpToken(null);
            $sellerModel->setRpTokenCreatedAt(null);
        }
        if (!array_key_exists('addresses', $sellerArr)
            && null !== $prevSellerDataArr
            && array_key_exists('default_billing', $prevSellerDataArr)
        ) {
            $sellerModel->setDefaultBilling($prevSellerDataArr['default_billing']);
        }
        if (!array_key_exists('addresses', $sellerArr)
            && null !== $prevSellerDataArr
            && array_key_exists('default_shipping', $prevSellerDataArr)
        ) {
            $sellerModel->setDefaultShipping($prevSellerDataArr['default_shipping']);
        }
        $this->setValidationFlag($sellerArr, $sellerModel);
        $sellerModel->save();
        $this->sellerRegistry->push($sellerModel);
        $sellerId = $sellerModel->getId();
        if (!$seller->getAddresses()
            && $delegatedNewOperation
            && $delegatedNewOperation->getSeller()->getAddresses()
        ) {
            $seller->setAddresses($delegatedNewOperation->getSeller()->getAddresses());
        }
        if ($seller->getAddresses() !== null && !$sellerModel->getData('ignore_validation_flag')) {
            if ($seller->getId()) {
                $existingAddresses = $this->getById($seller->getId())->getAddresses();
                $getIdFunc = function ($address) {
                    return $address->getId();
                };
                $existingAddressIds = array_map($getIdFunc, $existingAddresses);
            } else {
                $existingAddressIds = [];
            }
            $savedAddressIds = [];
            foreach ($seller->getAddresses() as $address) {
                $address->setSellerId($sellerId)
                    ->setRegion($address->getRegion());
                $this->addressRepository->save($address);
                if ($address->getId()) {
                    $savedAddressIds[] = $address->getId();
                }
            }
            $this->deleteAddressesByIds(array_diff($existingAddressIds, $savedAddressIds));
        }
        $this->sellerRegistry->remove($sellerId);
        $savedSeller = $this->get($seller->getEmail(), $seller->getWebsiteId());
        $this->eventManager->dispatch(
            'seller_save_after_data_object',
            [
                'seller_data_object' => $savedSeller,
                'orig_seller_data_object' => $prevSellerData,
                'delegate_data' => $delegatedNewOperation ? $delegatedNewOperation->getAdditionalData() : [],
            ]
        );
        return $savedSeller;
    }

    /**
     * Delete addresses by ids
     *
     * @param array $addressIds
     * @return void
     */
    private function deleteAddressesByIds(array $addressIds): void
    {
        foreach ($addressIds as $id) {
            $this->addressRepository->deleteById($id);
        }
    }

    /**
     * Validate seller group id if exist
     *
     * @param int|null $groupId
     * @return bool
     * @throws LocalizedException
     */
    private function validateGroupId(?int $groupId): bool
    {
        if ($groupId) {
            try {
                $this->groupRepository->getById($groupId);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('The specified seller group id does not exist.'));
            }
        }

        return true;
    }

    /**
     * Set secure data to seller model
     *
     * @param \Magento\Seller\Model\Seller $sellerModel
     * @param string|null $passwordHash
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return void
     */
    private function populateSellerWithSecureData($sellerModel, $passwordHash = null)
    {
        if ($sellerModel->getId()) {
            $sellerSecure = $this->sellerRegistry->retrieveSecureData($sellerModel->getId());

            $sellerModel->setRpToken($passwordHash ? null : $sellerSecure->getRpToken());
            $sellerModel->setRpTokenCreatedAt($passwordHash ? null : $sellerSecure->getRpTokenCreatedAt());
            $sellerModel->setPasswordHash($passwordHash ?: $sellerSecure->getPasswordHash());

            $sellerModel->setFailuresNum($sellerSecure->getFailuresNum());
            $sellerModel->setFirstFailure($sellerSecure->getFirstFailure());
            $sellerModel->setLockExpires($sellerSecure->getLockExpires());
        } elseif ($passwordHash) {
            $sellerModel->setPasswordHash($passwordHash);
        }

        if ($passwordHash && $sellerModel->getId()) {
            $this->sellerRegistry->remove($sellerModel->getId());
        }
    }

    /**
     * Retrieve seller.
     *
     * @param string $email
     * @param int|null $websiteId
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email, $websiteId = null)
    {
        $sellerModel = $this->sellerRegistry->retrieveByEmail($email, $websiteId);
        return $sellerModel->getDataModel();
    }

    /**
     * Get seller by Seller ID.
     *
     * @param int $sellerId
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sellerId)
    {
        $sellerModel = $this->sellerRegistry->retrieve($sellerId);
        return $sellerModel->getDataModel();
    }

    /**
     * Retrieve sellers which match a specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#SellerRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Seller\Api\Data\SellerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var \Magento\Seller\Model\ResourceModel\Seller\Collection $collection */
        $collection = $this->sellerFactory->create()->getCollection();
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            SellerInterface::class
        );
        // This is needed to make sure all the attributes are properly loaded
        foreach ($this->sellerMetadata->getAllAttributesMetadata() as $metadata) {
            $collection->addAttributeToSelect($metadata->getAttributeCode());
        }
        // Needed to enable filtering on name as a whole
        $collection->addNameToSelect();
        // Needed to enable filtering based on billing address attributes
        $collection->joinAttribute('billing_postcode', 'seller_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'seller_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'seller_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'seller_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'seller_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('billing_company', 'seller_address/company', 'default_billing', null, 'left');

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults->setTotalCount($collection->getSize());

        $sellers = [];
        /** @var \Magento\Seller\Model\Seller $sellerModel */
        foreach ($collection as $sellerModel) {
            $sellers[] = $sellerModel->getDataModel();
        }
        $searchResults->setItems($sellers);
        return $searchResults;
    }

    /**
     * Delete seller.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(SellerInterface $seller)
    {
        return $this->deleteById($seller->getId());
    }

    /**
     * Delete seller by Seller ID.
     *
     * @param int $sellerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sellerId)
    {
        $sellerModel = $this->sellerRegistry->retrieve($sellerId);
        $sellerModel->delete();
        $this->sellerRegistry->remove($sellerId);
        $this->notificationStorage->remove(NotificationStorage::UPDATE_SELLER_SESSION, $sellerId);

        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 101.0.0
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = ['attribute' => $filter->getField(), $condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * Set ignore_validation_flag to skip model validation
     *
     * @param array $sellerArray
     * @param Seller $sellerModel
     * @return void
     */
    private function setValidationFlag($sellerArray, $sellerModel)
    {
        if (isset($sellerArray['ignore_validation_flag'])) {
            $sellerModel->setData('ignore_validation_flag', true);
        }
    }

    /**
     * Set seller group id
     *
     * @param Seller $sellerModel
     * @param array $sellerArr
     * @param array $prevSellerDataArr
     */
    private function setSellerGroupId($sellerModel, $sellerArr, $prevSellerDataArr)
    {
        if (!isset($sellerArr['group_id']) && $prevSellerDataArr && isset($prevSellerDataArr['group_id'])) {
            $sellerModel->setGroupId($prevSellerDataArr['group_id']);
        }
    }
}
