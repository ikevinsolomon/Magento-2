<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit\Tab\View;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Controller\RegistryConstants;
use Magento\Seller\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Seller\Model\Seller;

/**
 * Adminhtml seller view personal information sales block.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfo extends \Magento\Backend\Block\Template
{
    /**
     * Interval in minutes that shows how long seller will be marked 'Online'
     * since his last activity. Used only if it's impossible to get such setting
     * from configuration.
     */
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;

    /**
     * Seller
     *
     * @var \Magento\Seller\Api\Data\SellerInterface
     */
    protected $seller;

    /**
     * Seller log
     *
     * @var \Magento\Seller\Model\Log
     */
    protected $sellerLog;

    /**
     * Seller logger
     *
     * @var \Magento\Seller\Model\Logger
     */
    protected $sellerLogger;

    /**
     * Seller registry
     *
     * @var \Magento\Seller\Model\SellerRegistry
     */
    protected $sellerRegistry;

    /**
     * Account management
     *
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * Seller group repository
     *
     * @var \Magento\Seller\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Seller data factory
     *
     * @var \Magento\Seller\Api\Data\SellerInterfaceFactory
     */
    protected $sellerDataFactory;

    /**
     * Address helper
     *
     * @var \Magento\Seller\Helper\Address
     */
    protected $addressHelper;

    /**
     * Date time
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Address mapper
     *
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Seller\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Seller\Api\Data\SellerInterfaceFactory $sellerDataFactory
     * @param \Magento\Seller\Helper\Address $addressHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param Mapper $addressMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Seller\Model\Logger $sellerLogger
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        AccountManagementInterface $accountManagement,
        \Magento\Seller\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Seller\Api\Data\SellerInterfaceFactory $sellerDataFactory,
        \Magento\Seller\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Seller\Model\Logger $sellerLogger,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->accountManagement = $accountManagement;
        $this->groupRepository = $groupRepository;
        $this->sellerDataFactory = $sellerDataFactory;
        $this->addressHelper = $addressHelper;
        $this->dateTime = $dateTime;
        $this->addressMapper = $addressMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->sellerLogger = $sellerLogger;

        parent::__construct($context, $data);
    }

    /**
     * Set seller registry
     *
     * @param \Magento\Framework\Registry $sellerRegistry
     * @return void
     * @deprecated 100.1.0
     */
    public function setSellerRegistry(\Magento\Seller\Model\SellerRegistry $sellerRegistry)
    {
        $this->sellerRegistry = $sellerRegistry;
    }

    /**
     * Get seller registry
     *
     * @return \Magento\Seller\Model\SellerRegistry
     * @deprecated 100.1.0
     */
    public function getSellerRegistry()
    {

        if (!($this->sellerRegistry instanceof \Magento\Seller\Model\SellerRegistry)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Seller\Model\SellerRegistry::class
            );
        } else {
            return $this->sellerRegistry;
        }
    }

    /**
     * Retrieve seller object
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     */
    public function getSeller()
    {
        if (!$this->seller) {
            $this->seller = $this->sellerDataFactory->create();
            $data = $this->_backendSession->getSellerData();
            $this->dataObjectHelper->populateWithArray(
                $this->seller,
                $data['account'],
                \Magento\Seller\Api\Data\SellerInterface::class
            );
        }
        return $this->seller;
    }

    /**
     * Retrieve seller id
     *
     * @return string|null
     */
    public function getSellerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_SELLER_ID);
    }

    /**
     * Retrieves seller log model
     *
     * @return \Magento\Seller\Model\Log
     */
    protected function getSellerLog()
    {
        if (!$this->sellerLog) {
            $this->sellerLog = $this->sellerLogger->get(
                $this->getSeller()->getId()
            );
        }

        return $this->sellerLog;
    }

    /**
     * Returns seller's created date in the assigned store
     *
     * @return string
     */
    public function getStoreCreateDate()
    {
        $createdAt = $this->getSeller()->getCreatedAt();
        try {
            return $this->formatDate(
                $createdAt,
                \IntlDateFormatter::MEDIUM,
                true,
                $this->getStoreCreateDateTimezone()
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return '';
        }
    }

    /**
     * Retrieve store default timezone from configuration
     *
     * @return string
     */
    public function getStoreCreateDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getSeller()->getStoreId()
        );
    }

    /**
     * Get seller creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return $this->formatDate(
            $this->getSeller()->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    /**
     * Check if account is confirmed
     *
     * @return \Magento\Framework\Phrase
     */
    public function getIsConfirmedStatus()
    {
        $id = $this->getSellerId();
        switch ($this->accountManagement->getConfirmationStatus($id)) {
            case AccountManagementInterface::ACCOUNT_CONFIRMED:
                return __('Confirmed');
            case AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED:
                return __('Confirmation Required');
            case AccountManagementInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED:
                return __('Confirmation Not Required');
        }
        return __('Indeterminate');
    }

    /**
     * Retrieve store
     *
     * @return null|string
     */
    public function getCreatedInStore()
    {
        return $this->_storeManager->getStore(
            $this->getSeller()->getStoreId()
        )->getName();
    }

    /**
     * Retrieve billing address html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getBillingAddressHtml()
    {
        try {
            $address = $this->accountManagement->getDefaultBillingAddress($this->getSeller()->getId());
        } catch (NoSuchEntityException $e) {
            return $this->escapeHtml(__('The seller does not have default billing address.'));
        }

        if ($address === null) {
            return $this->escapeHtml(__('The seller does not have default billing address.'));
        }

        return $this->addressHelper->getFormatTypeRenderer(
            'html'
        )->renderArray(
            $this->addressMapper->toFlatArray($address)
        );
    }

    /**
     * Retrieve group name
     *
     * @return string|null
     */
    public function getGroupName()
    {
        $seller = $this->getSeller();
        if ($groupId = $seller->getId() ? $seller->getGroupId() : null) {
            if ($group = $this->getGroup($groupId)) {
                return $group->getCode();
            }
        }

        return null;
    }

    /**
     * Retrieve seller group by id
     *
     * @param int $groupId
     * @return \Magento\Seller\Api\Data\GroupInterface|null
     */
    private function getGroup($groupId)
    {
        try {
            $group = $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            $group = null;
        }
        return $group;
    }

    /**
     * Returns timezone of the store to which seller assigned.
     *
     * @return string
     */
    public function getStoreLastLoginDateTimezone()
    {
        return $this->_scopeConfig->getValue(
            $this->_localeDate->getDefaultTimezonePath(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getSeller()->getStoreId()
        );
    }

    /**
     * Get seller's current status.
     *
     * Seller considered 'Offline' in the next cases:
     *
     * - seller has never been logged in;
     * - seller clicked 'Log Out' link\button;
     * - predefined interval has passed since seller's last activity.
     *
     * In all other cases seller considered 'Online'.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getCurrentStatus()
    {
        $lastLoginTime = $this->getSellerLog()->getLastLoginAt();

        // Seller has never been logged in.
        if (!$lastLoginTime) {
            return __('Offline');
        }

        $lastLogoutTime = $this->getSellerLog()->getLastLogoutAt();

        // Seller clicked 'Log Out' link\button.
        if ($lastLogoutTime && strtotime($lastLogoutTime) > strtotime($lastLoginTime)) {
            return __('Offline');
        }

        // Predefined interval has passed since seller's last activity.
        $interval = $this->getOnlineMinutesInterval();
        $currentTimestamp = (new \DateTime())->getTimestamp();
        $lastVisitTime = $this->getSellerLog()->getLastVisitAt();

        if ($lastVisitTime && $currentTimestamp - strtotime($lastVisitTime) > $interval * 60) {
            return __('Offline');
        }

        return __('Online');
    }

    /**
     * Get seller last login date.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getLastLoginDate()
    {
        $date = $this->getSellerLog()->getLastLoginAt();

        if ($date) {
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }

    /**
     * Returns seller last login date in store's timezone.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getStoreLastLoginDate()
    {
        $date = strtotime($this->getSellerLog()->getLastLoginAt());

        if ($date) {
            $date = $this->_localeDate->scopeDate($this->getSeller()->getStoreId(), $date, true);
            return $this->formatDate($date, \IntlDateFormatter::MEDIUM, true);
        }

        return __('Never');
    }

    /**
     * Returns interval that shows how long seller will be considered 'Online'.
     *
     * @return int Interval in minutes
     */
    protected function getOnlineMinutesInterval()
    {
        $configValue = $this->_scopeConfig->getValue(
            'seller/online_sellers/online_minutes_interval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return (int)$configValue > 0 ? (int)$configValue : self::DEFAULT_ONLINE_MINUTES_INTERVAL;
    }

    /**
     * Get seller account lock status
     *
     * @return \Magento\Framework\Phrase
     */
    public function getAccountLock()
    {
        $sellerModel = $this->getSellerRegistry()->retrieve($this->getSellerId());
        $sellerStatus = __('Unlocked');
        if ($sellerModel->isSellerLocked()) {
            $sellerStatus = __('Locked');
        }
        return $sellerStatus;
    }
}
