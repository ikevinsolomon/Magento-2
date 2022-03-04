<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Seller\Api\AddressMetadataInterface;
use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Seller\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Indexer\StateInterface;

/**
 * Seller address model
 *
 * @api
 * @method int getParentId()
 * @method \Magento\Seller\Model\Address setParentId(int $parentId)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Address extends \Magento\Seller\Model\Address\AbstractAddress
{
    /**
     * Seller entity
     *
     * @var Seller
     */
    protected $_seller;

    /**
     * @var SellerFactory
     */
    protected $_sellerFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Seller\Model\Address\CustomAttributeListInterface
     */
    private $attributeList;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param SellerFactory $sellerFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Seller\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        SellerFactory $sellerFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->_sellerFactory = $sellerFactory;
        $this->indexerRegistry = $indexerRegistry;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $metadataService,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectHelper,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize address model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Seller\Model\ResourceModel\Address::class);
    }

    /**
     * Update Model with the data from Data Interface
     *
     * @param AddressInterface $address
     * @return $this
     * Use Api/RepositoryInterface for the operations in the Data Interfaces. Don't rely on Address Model
     */
    public function updateData(AddressInterface $address)
    {
        // Set all attributes
        $attributes = $this->dataProcessor
            ->buildOutputDataArray($address, \Magento\Seller\Api\Data\AddressInterface::class);

        foreach ($attributes as $attributeCode => $attributeData) {
            if (AddressInterface::REGION === $attributeCode) {
                $this->setRegion($address->getRegion()->getRegion());
                $this->setRegionCode($address->getRegion()->getRegionCode());
                $this->setRegionId($address->getRegion()->getRegionId());
            } else {
                $this->setDataUsingMethod($attributeCode, $attributeData);
            }
        }
        // Need to explicitly set this due to discrepancy in the keys between model and data object
        $this->setIsDefaultBilling($address->isDefaultBilling());
        $this->setIsDefaultShipping($address->isDefaultShipping());
        $customAttributes = $address->getCustomAttributes();
        if ($customAttributes !== null) {
            foreach ($customAttributes as $attribute) {
                $this->setData($attribute->getAttributeCode(), $attribute->getValue());
            }
        }

        return $this;
    }

    /**
     * Create address data object based on current address model.
     *
     * @param int|null $defaultBillingAddressId
     * @param int|null $defaultShippingAddressId
     * @return AddressInterface
     * Use Api/Data/AddressInterface as a result of service operations. Don't rely on the model to provide
     * the instance of Api/Data/AddressInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDataModel($defaultBillingAddressId = null, $defaultShippingAddressId = null)
    {
        if ($this->getSellerId() || $this->getParentId()) {
            $seller = $this->getSeller();
            $defaultBillingAddressId = $seller->getDefaultBilling() ?: $defaultBillingAddressId;
            $defaultShippingAddressId = $seller->getDefaultShipping() ?: $defaultShippingAddressId;
        }
        return parent::getDataModel($defaultBillingAddressId, $defaultShippingAddressId);
    }

    /**
     * Retrieve address seller identifier
     *
     * @return int
     */
    public function getSellerId()
    {
        return $this->_getData('seller_id') ? $this->_getData('seller_id') : $this->getParentId();
    }

    /**
     * Declare address seller identifier
     *
     * @param int $id
     * @return $this
     */
    public function setSellerId($id)
    {
        $this->setParentId($id);
        $this->setData('seller_id', $id);
        return $this;
    }

    /**
     * Retrieve address seller
     *
     * @return Seller|false
     */
    public function getSeller()
    {
        if (!$this->getSellerId()) {
            return false;
        }
        if (empty($this->_seller)) {
            $this->_seller = $this->_createSeller()->load($this->getSellerId());
        }
        return $this->_seller;
    }

    /**
     * Specify address seller
     *
     * @param Seller $seller
     * @return $this
     */
    public function setSeller(Seller $seller)
    {
        $this->_seller = $seller;
        $this->setSellerId($seller->getId());
        return $this;
    }

    /**
     * Retrieve address entity attributes
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        $attributes = $this->getData('attributes');
        if ($attributes === null) {
            $attributes = $this->_getResource()->loadAllAttributes($this)->getSortedAttributes();
            $this->setData('attributes', $attributes);
        }
        return $attributes;
    }

    /**
     * Get attributes created by default
     *
     * @return string[]
     */
    public function getDefaultAttributeCodes()
    {
        return $this->_getResource()->getDefaultAttributes();
    }

    /**
     * Clone address
     *
     * @return void
     */
    public function __clone()
    {
        $this->setId(null);
    }

    /**
     * Return Entity Type instance
     *
     * @return \Magento\Eav\Model\Entity\Type
     */
    public function getEntityType()
    {
        return $this->_getResource()->getEntityType();
    }

    /**
     * Return Region ID
     *
     * @return int
     */
    public function getRegionId()
    {
        return (int)$this->getData('region_id');
    }

    /**
     * Set Region ID. $regionId is automatically converted to integer
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->setData('region_id', (int)$regionId);
        return $this;
    }

    /**
     * Create seller model
     *
     * @return Seller
     */
    protected function _createSeller()
    {
        return $this->_sellerFactory->create();
    }

    /**
     * Return Entity Type ID
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        return $this->getEntityType()->getId();
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        $indexer = $this->indexerRegistry->get(Seller::SELLER_GRID_INDEXER_ID);
        if ($indexer->getState()->getStatus() == StateInterface::STATUS_VALID) {
            $this->_getResource()->addCommitCallback([$this, 'reindex']);
        }
        return parent::afterSave();
    }

    /**
     * Init indexing process after seller delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterDeleteCommit()
    {
        $this->reindex();
        return parent::afterDeleteCommit();
    }

    /**
     * Init indexing process after seller save
     *
     * @return void
     */
    public function reindex()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get(Seller::SELLER_GRID_INDEXER_ID);
        $indexer->reindexRow($this->getSellerId());
    }

    /**
     * Get a list of custom attribute codes.
     *
     * By default, entity can be extended only using extension attributes functionality.
     *
     * @return string[]
     * @since 100.0.6
     */
    protected function getCustomAttributesCodes()
    {
        return array_keys($this->getAttributeList()->getAttributes());
    }

    /**
     * Get new AttributeList dependency for application code.
     *
     * @return \Magento\Seller\Model\Address\CustomAttributeListInterface
     * @deprecated 100.0.6
     */
    private function getAttributeList()
    {
        if (!$this->attributeList) {
            $this->attributeList = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Seller\Model\Address\CustomAttributeListInterface::class
            );
        }
        return $this->attributeList;
    }

    /**
     * Retrieve attribute set id for seller address.
     *
     * @return int
     * @since 101.0.0
     */
    public function getAttributeSetId()
    {
        return parent::getAttributeSetId() ?: AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS;
    }
}
