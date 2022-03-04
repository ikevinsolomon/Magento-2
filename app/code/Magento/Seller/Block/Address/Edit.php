<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Address;

use Magento\Seller\Api\AddressMetadataInterface;
use Magento\Seller\Helper\Address;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Seller address edit block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Edit extends \Magento\Directory\Block\Data
{
    /**
     * @var \Magento\Seller\Api\Data\AddressInterface|null
     */
    protected $_address = null;

    /**
     * @var \Magento\Seller\Model\Session
     */
    protected $_sellerSession;

    /**
     * @var \Magento\Seller\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \Magento\Seller\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Seller\Helper\Session\CurrentSeller
     */
    protected $currentSeller;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Seller\Model\Session $sellerSession
     * @param \Magento\Seller\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Seller\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Seller\Helper\Session\CurrentSeller $currentSeller
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array $data
     * @param AddressMetadataInterface|null $addressMetadata
     * @param Address|null $addressHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Seller\Model\Session $sellerSession,
        \Magento\Seller\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Seller\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Seller\Helper\Session\CurrentSeller $currentSeller,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = [],
        AddressMetadataInterface $addressMetadata = null,
        Address $addressHelper = null
    ) {
        $this->_sellerSession = $sellerSession;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->currentSeller = $currentSeller;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressMetadata = $addressMetadata ?: ObjectManager::getInstance()->get(AddressMetadataInterface::class);
        $data['addressHelper'] = $addressHelper ?: ObjectManager::getInstance()->get(Address::class);
        $data['directoryHelper'] = $directoryHelper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare the layout of the address edit block.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->initAddressObject();

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_sellerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region_id' => isset($postedData['region_id']) ? $postedData['region_id'] : null,
                'region' => $postedData['region'],
            ];
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                \Magento\Seller\Api\Data\AddressInterface::class
            );
        }
        $this->precheckRequiredAttributes();
        return $this;
    }

    /**
     * Initialize address object.
     *
     * @return void
     */
    private function initAddressObject()
    {
        // Init address object
        if ($addressId = $this->getRequest()->getParam('id')) {
            try {
                $this->_address = $this->_addressRepository->getById($addressId);
                if ($this->_address->getSellerId() != $this->_sellerSession->getSellerId()) {
                    $this->_address = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->_address = null;
            }
        }

        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->addressDataFactory->create();
            $seller = $this->getSeller();
            $this->_address->setPrefix($seller->getPrefix());
            $this->_address->setFirstname($seller->getFirstname());
            $this->_address->setMiddlename($seller->getMiddlename());
            $this->_address->setLastname($seller->getLastname());
            $this->_address->setSuffix($seller->getSuffix());
        }
    }

    /**
     * Precheck attributes that may be required in attribute configuration.
     *
     * @return void
     */
    private function precheckRequiredAttributes()
    {
        $precheckAttributes = $this->getData('check_attributes_on_render');
        $requiredAttributesPrechecked = [];
        if (!empty($precheckAttributes) && is_array($precheckAttributes)) {
            foreach ($precheckAttributes as $attributeCode) {
                $attributeMetadata = $this->addressMetadata->getAttributeMetadata($attributeCode);
                if ($attributeMetadata && $attributeMetadata->isRequired()) {
                    $requiredAttributesPrechecked[$attributeCode] = $attributeCode;
                }
            }
        }
        $this->setData('required_attributes_prechecked', $requiredAttributesPrechecked);
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        $nameBlock = $this->getLayout()
            ->createBlock(\Magento\Seller\Block\Widget\Name::class)
            ->setObject($this->getAddress());

        return $nameBlock->toHtml();
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = __('Edit Address');
        } else {
            $title = __('Add New Address');
        }
        return $title;
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        if ($this->getSellerAddressCount()) {
            return $this->getUrl('seller/address');
        } else {
            return $this->getUrl('seller/account/');
        }
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'seller/address/formPost',
            ['_secure' => true, 'id' => $this->getAddress()->getId()]
        );
    }

    /**
     * Return the associated address.
     *
     * @return \Magento\Seller\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the country Id.
     *
     * @return int|null|string
     */
    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region->getRegion();
    }

    /**
     * Return the id of the region being edited.
     *
     * @return int region id
     */
    public function getRegionId()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? 0 : $region->getRegionId();
    }

    /**
     * Retrieve the number of addresses associated with the seller given a seller Id.
     *
     * @return int
     */
    public function getSellerAddressCount()
    {
        return count($this->getSeller()->getAddresses());
    }

    /**
     * Determine if the address can be set as the default billing address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getSellerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    /**
     * Determine if the address can be set as the default shipping address.
     *
     * @return bool|int
     */
    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getSellerAddressCount();
        }
        return !$this->isDefaultShipping();
    }

    /**
     * Is the address the default billing address?
     *
     * @return bool
     */
    public function isDefaultBilling()
    {
        return (bool)$this->getAddress()->isDefaultBilling();
    }

    /**
     * Is the address the default shipping address?
     *
     * @return bool
     */
    public function isDefaultShipping()
    {
        return (bool)$this->getAddress()->isDefaultShipping();
    }

    /**
     * Retrieve the Seller Data using the seller Id from the seller session.
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     */
    public function getSeller()
    {
        return $this->currentSeller->getSeller();
    }

    /**
     * Return back button Url, either to seller address or account.
     *
     * @return string
     */
    public function getBackButtonUrl()
    {
        if ($this->getSellerAddressCount()) {
            return $this->getUrl('seller/address');
        } else {
            return $this->getUrl('seller/account/');
        }
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
