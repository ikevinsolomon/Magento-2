<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * Seller group resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Group extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb
{
    /**
     * Group Management
     *
     * @var \Magento\Seller\Api\GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var \Magento\Seller\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $_sellersFactory;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param \Magento\Seller\Api\GroupManagementInterface $groupManagement
     * @param Seller\CollectionFactory $sellersFactory
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \Magento\Seller\Api\GroupManagementInterface $groupManagement,
        \Magento\Seller\Model\ResourceModel\Seller\CollectionFactory $sellersFactory,
        $connectionName = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_sellersFactory = $sellersFactory;
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('seller_group', 'seller_group_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => 'seller_group_code', 'title' => __('Seller Group')]];

        return $this;
    }

    /**
     * Check if group uses as default
     *
     * @param  \Magento\Framework\Model\AbstractModel $group
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        if ($group->usesAsDefault()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t delete group "%1".', $group->getCode())
            );
        }
        return parent::_beforeDelete($group);
    }

    /**
     * Method set default group id to the sellers collection
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $group)
    {
        $sellerCollection = $this->_createSellersCollection()->addAttributeToFilter(
            'group_id',
            $group->getId()
        )->load();
        foreach ($sellerCollection as $seller) {
            /** @var $seller \Magento\Seller\Model\Seller */
            $seller->load($seller->getId());
            $defaultGroupId = $this->_groupManagement->getDefaultGroup($seller->getStoreId())->getId();
            $seller->setGroupId($defaultGroupId);
            $seller->save();
        }
        return parent::_afterDelete($group);
    }

    /**
     * Create sellers collection.
     *
     * @return \Magento\Seller\Model\ResourceModel\Seller\Collection
     */
    protected function _createSellersCollection()
    {
        return $this->_sellersFactory->create();
    }

    /**
     * Prepare data before save
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $group)
    {
        /** @var \Magento\Seller\Model\Group $group */
        $group->setCode(substr($group->getCode(), 0, $group::GROUP_CODE_MAX_LENGTH));
        return parent::_beforeSave($group);
    }

    /**
     * @inheritdoc
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId() == \Magento\Seller\Model\Group::CUST_GROUP_ALL) {
            $this->skipReservedId($object);
        }

        return $this;
    }

    /**
     * Here we do not allow to save systems reserved ID.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function skipReservedId(\Magento\Framework\Model\AbstractModel $object)
    {
        $tableFieldsWithoutIdField = $this->getTableFieldsWithoutIdField();
        $select = $this->getConnection()->select();
        $select->from(
            [$this->getMainTable()],
            $tableFieldsWithoutIdField
        )
            ->where('seller_group_id = ?', \Magento\Seller\Model\Group::CUST_GROUP_ALL);

        $query = $this->getConnection()->insertFromSelect(
            $select,
            $this->getMainTable(),
            $tableFieldsWithoutIdField
        );
        $this->getConnection()->query($query);
        $lastInsertId = $this->getConnection()->lastInsertId();

        $query = $this->getConnection()->deleteFromSelect(
            $select,
            $this->getMainTable()
        );
        $this->getConnection()->query($query);

        $object->setId($lastInsertId);
    }

    /**
     * Get main table fields except of ID field.
     *
     * @return array
     */
    private function getTableFieldsWithoutIdField()
    {
        $fields = $this->getConnection()->describeTable($this->getMainTable());
        if (isset($fields['seller_group_id'])) {
            unset($fields['seller_group_id']);
        }

        return array_keys($fields);
    }
}
