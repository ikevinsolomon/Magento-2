<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Widget;

use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\Data\OptionInterface;

/**
 * Block to render seller's gender attribute
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Gender extends AbstractWidget
{
    /**
     * @var \Magento\Seller\Model\Session
     */
    protected $_sellerSession;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * Create an instance of the Gender widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Seller\Helper\Address $addressHelper
     * @param SellerMetadataInterface $sellerMetadata
     * @param SellerRepositoryInterface $sellerRepository
     * @param \Magento\Seller\Model\Session $sellerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Seller\Helper\Address $addressHelper,
        SellerMetadataInterface $sellerMetadata,
        SellerRepositoryInterface $sellerRepository,
        \Magento\Seller\Model\Session $sellerSession,
        array $data = []
    ) {
        $this->_sellerSession = $sellerSession;
        $this->sellerRepository = $sellerRepository;
        parent::__construct($context, $addressHelper, $sellerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize block
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Seller::widget/gender.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isVisible() : false;
    }

    /**
     * Check if gender attribute marked as required
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isRequired() : false;
    }

    /**
     * Retrieve store attribute label
     *
     * @param string $attributeCode
     *
     * @return string
     */
    public function getStoreLabel($attributeCode)
    {
        $attribute = $this->_getAttribute($attributeCode);
        return $attribute ? __($attribute->getStoreLabel()) : '';
    }

    /**
     * Get current seller from session
     *
     * @return SellerInterface
     */
    public function getSeller()
    {
        return $this->sellerRepository->getById($this->_sellerSession->getSellerId());
    }

    /**
     * Returns options from gender attribute
     *
     * @return OptionInterface[]
     */
    public function getGenderOptions()
    {
        return $this->_getAttribute('gender')->getOptions();
    }
}
