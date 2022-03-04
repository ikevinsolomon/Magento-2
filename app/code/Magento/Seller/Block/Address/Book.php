<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Address;

use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Model\Address\Mapper;
use Magento\Seller\Block\Address\Grid as AddressesGrid;

/**
 * Seller address book block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Book extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Seller\Helper\Session\CurrentSeller
     */
    protected $currentSeller;

    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Seller\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var AddressesGrid
     */
    private $addressesGrid;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param SellerRepositoryInterface|null $sellerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Seller\Helper\Session\CurrentSeller $currentSeller
     * @param \Magento\Seller\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     * @param AddressesGrid|null $addressesGrid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Seller\Api\SellerRepositoryInterface $sellerRepository = null,
        AddressRepositoryInterface $addressRepository,
        \Magento\Seller\Helper\Session\CurrentSeller $currentSeller,
        \Magento\Seller\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = [],
        Grid $addressesGrid = null
    ) {
        $this->currentSeller = $currentSeller;
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->addressesGrid = $addressesGrid ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(AddressesGrid::class);
        parent::__construct($context, $data);
    }

    /**
     * Prepare the Address Book section layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Address Book'));
        return parent::_prepareLayout();
    }

    /**
     * Generate and return "New Address" URL
     *
     * @return string
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Seller\Block\Address\Grid::getAddAddressUrl
     */
    public function getAddAddressUrl()
    {
        return $this->addressesGrid->getAddAddressUrl();
    }

    /**
     * Generate and return "Back" URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('seller/account/', ['_secure' => true]);
    }

    /**
     * Generate and return "Delete" URL
     *
     * @return string
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Seller\Block\Address\Grid::getDeleteUrl
     */
    public function getDeleteUrl()
    {
        return $this->addressesGrid->getDeleteUrl();
    }

    /**
     * Generate and return "Edit Address" URL.
     *
     * Address ID passed in parameters
     *
     * @param int $addressId
     * @return string
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Seller\Block\Address\Grid::getAddressEditUrl
     */
    public function getAddressEditUrl($addressId)
    {
        return $this->addressesGrid->getAddressEditUrl($addressId);
    }

    /**
     * Determines is the address primary (billing or shipping)
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasPrimaryAddress()
    {
        return $this->getDefaultBilling() || $this->getDefaultShipping();
    }

    /**
     * Get current additional seller addresses
     *
     * Will return array of address interfaces if seller have additional addresses and false in other case.
     *
     * @return \Magento\Seller\Api\Data\AddressInterface[]|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated 102.0.1 not used in this block
     * @see \Magento\Seller\Block\Address\Grid::getAdditionalAddresses
     */
    public function getAdditionalAddresses()
    {
        try {
            $addresses = $this->addressesGrid->getAdditionalAddresses();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
        return empty($addresses) ? false : $addresses;
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressHtml(\Magento\Seller\Api\Data\AddressInterface $address = null)
    {
        if ($address !== null) {
            /** @var \Magento\Seller\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return '';
    }

    /**
     * Get current seller
     *
     * @return \Magento\Seller\Api\Data\SellerInterface|null
     */
    public function getSeller()
    {
        $seller = null;
        try {
            $seller = $this->currentSeller->getSeller();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
        return $seller;
    }

    /**
     * Get seller's default billing address
     *
     * @return int|null
     */
    public function getDefaultBilling()
    {
        $seller = $this->getSeller();
        if ($seller === null) {
            return null;
        } else {
            return $seller->getDefaultBilling();
        }
    }

    /**
     * Get seller address by ID
     *
     * @param int $addressId
     * @return \Magento\Seller\Api\Data\AddressInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAddressById($addressId)
    {
        try {
            return $this->addressRepository->getById($addressId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get seller's default shipping address
     *
     * @return int|null
     */
    public function getDefaultShipping()
    {
        $seller = $this->getSeller();
        if ($seller === null) {
            return null;
        } else {
            return $seller->getDefaultShipping();
        }
    }
}
