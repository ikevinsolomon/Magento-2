<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\ResourceModel;

use Magento\Seller\Model\AccountConfirmation;
use Magento\Seller\Model\Seller\NotificationStorage;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Seller entity resource model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Seller extends \Magento\Eav\Model\Entity\VersionControl\AbstractEntity
{
    /**
     * @var \Magento\Framework\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * Seller constructor.
     *
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Validator\Factory $validatorFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     * @param AccountConfirmation $accountConfirmation
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $data = [],
        AccountConfirmation $accountConfirmation = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $data);

        $this->_scopeConfig = $scopeConfig;
        $this->_validatorFactory = $validatorFactory;
        $this->dateTime = $dateTime;
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        $this->setType('seller');
        $this->setConnection('seller_read');
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve seller entity default attributes
     *
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return [
            'created_at',
            'updated_at',
            'increment_id',
            'store_id',
            'website_id'
        ];
    }

    /**
     * Check seller scope, email and confirmation key before saving
     *
     * @param \Magento\Framework\DataObject|\Magento\Seller\Api\Data\SellerInterface $seller
     *
     * @return $this
     * @throws AlreadyExistsException
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $seller)
    {
        /** @var \Magento\Seller\Model\Seller $seller */
        if ($seller->getStoreId() === null) {
            $seller->setStoreId($this->storeManager->getStore()->getId());
        }
        $seller->getGroupId();

        parent::_beforeSave($seller);

        if (!$seller->getEmail()) {
            throw new ValidatorException(__('The seller email is missing. Enter and try again.'));
        }

        $connection = $this->getConnection();
        $bind = ['email' => $seller->getEmail()];

        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :email'
        );
        if ($seller->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$seller->getWebsiteId();
            $select->where('website_id = :website_id');
        }
        if ($seller->getId()) {
            $bind['entity_id'] = (int)$seller->getId();
            $select->where('entity_id != :entity_id');
        }

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            throw new AlreadyExistsException(
                __('A seller with the same email address already exists in an associated website.')
            );
        }

        // set confirmation key logic
        if (!$seller->getId() &&
            $this->accountConfirmation->isConfirmationRequired(
                $seller->getWebsiteId(),
                $seller->getId(),
                $seller->getEmail()
            )
        ) {
            $seller->setConfirmation($seller->getRandomConfirmationKey());
        }
        // remove seller confirmation key from database, if empty
        if (!$seller->getConfirmation()) {
            $seller->setConfirmation(null);
        }

        if (!$seller->getData('ignore_validation_flag')) {
            $this->_validate($seller);
        }

        return $this;
    }

    /**
     * Validate seller entity
     *
     * @param \Magento\Seller\Model\Seller $seller
     * @return void
     * @throws ValidatorException
     */
    protected function _validate($seller)
    {
        $validator = $this->_validatorFactory->createValidator('seller', 'save');

        if (!$validator->isValid($seller)) {
            throw new ValidatorException(
                null,
                null,
                $validator->getMessages()
            );
        }
    }

    /**
     * Retrieve notification storage
     *
     * @return NotificationStorage
     */
    private function getNotificationStorage()
    {
        if ($this->notificationStorage === null) {
            $this->notificationStorage = ObjectManager::getInstance()->get(NotificationStorage::class);
        }
        return $this->notificationStorage;
    }

    /**
     * Save seller addresses and set default addresses in attributes backend
     *
     * @param \Magento\Framework\DataObject $seller
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $seller)
    {
        $this->getNotificationStorage()->add(
            NotificationStorage::UPDATE_SELLER_SESSION,
            $seller->getId()
        );
        return parent::_afterSave($seller);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param \Magento\Framework\DataObject $object
     * @param string|int $rowId
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = parent::_getLoadRowSelect($object, $rowId);
        if ($object->getWebsiteId() && $object->getSharingConfig()->isWebsiteScope()) {
            $select->where('website_id =?', (int)$object->getWebsiteId());
        }

        return $select;
    }

    /**
     * Load seller by email
     *
     * @param \Magento\Seller\Model\Seller $seller
     * @param string $email
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail(\Magento\Seller\Model\Seller $seller, $email)
    {
        $connection = $this->getConnection();
        $bind = ['seller_email' => $email];
        $select = $connection->select()->from(
            $this->getEntityTable(),
            [$this->getEntityIdField()]
        )->where(
            'email = :seller_email'
        );

        if ($seller->getSharingConfig()->isWebsiteScope()) {
            if (!$seller->hasData('website_id')) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("A seller website ID wasn't specified. The ID must be specified to use the website scope.")
                );
            }
            $bind['website_id'] = (int)$seller->getWebsiteId();
            $select->where('website_id = :website_id');
        }

        $sellerId = $connection->fetchOne($select, $bind);
        if ($sellerId) {
            $this->load($seller, $sellerId);
        } else {
            $seller->setData([]);
        }

        return $this;
    }

    /**
     * Change seller password
     *
     * @param \Magento\Seller\Model\Seller $seller
     * @param string $newPassword
     * @return $this
     */
    public function changePassword(\Magento\Seller\Model\Seller $seller, $newPassword)
    {
        $seller->setPassword($newPassword);
        return $this;
    }

    /**
     * Check whether there are email duplicates of sellers in global scope
     *
     * @return bool
     */
    public function findEmailDuplicates()
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('seller_entity'),
            ['email', 'cnt' => 'COUNT(*)']
        )->group(
            'email'
        )->order(
            'cnt DESC'
        )->limit(
            1
        );
        $lookup = $connection->fetchRow($select);
        if (empty($lookup)) {
            return false;
        }
        return $lookup['cnt'] > 1;
    }

    /**
     * Check seller by id
     *
     * @param int $sellerId
     * @return bool
     */
    public function checkSellerId($sellerId)
    {
        $connection = $this->getConnection();
        $bind = ['entity_id' => (int)$sellerId];
        $select = $connection->select()->from(
            $this->getTable('seller_entity'),
            'entity_id'
        )->where(
            'entity_id = :entity_id'
        )->limit(
            1
        );

        $result = $connection->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Get seller website id
     *
     * @param int $sellerId
     * @return int
     */
    public function getWebsiteId($sellerId)
    {
        $connection = $this->getConnection();
        $bind = ['entity_id' => (int)$sellerId];
        $select = $connection->select()->from(
            $this->getTable('seller_entity'),
            'website_id'
        )->where(
            'entity_id = :entity_id'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Custom setter of increment ID if its needed
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setNewIncrementId(\Magento\Framework\DataObject $object)
    {
        if ($this->_scopeConfig->getValue(
            \Magento\Seller\Model\Seller::XML_PATH_GENERATE_HUMAN_FRIENDLY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            parent::setNewIncrementId($object);
        }
        return $this;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token and its creation time
     *
     * @param \Magento\Seller\Model\Seller $seller
     * @param string $passwordLinkToken
     * @return $this
     */
    public function changeResetPasswordLinkToken(\Magento\Seller\Model\Seller $seller, $passwordLinkToken)
    {
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $seller->setRpToken($passwordLinkToken);
            $seller->setRpTokenCreatedAt(
                (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            );
        }
        return $this;
    }
}
