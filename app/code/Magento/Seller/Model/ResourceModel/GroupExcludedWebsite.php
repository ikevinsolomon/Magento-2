<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Excluded seller group website resource model.
 */
class GroupExcludedWebsite extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('seller_group_excluded_website', 'entity_id');
    }

    /**
     * Retrieve excluded website ids related to seller group.
     *
     * @param int $sellerGroupId
     * @return array
     * @throws LocalizedException
     */
    public function loadSellerGroupExcludedWebsites(int $sellerGroupId): array
    {
        $connection = $this->getConnection();
        $bind = ['seller_group_id' => $sellerGroupId];

        $select = $connection->select()->from(
            $this->getMainTable(),
            ['website_id']
        )->where(
            'seller_group_id = :seller_group_id'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Retrieve all excluded website ids per related seller group.
     *
     * @return array
     * @throws LocalizedException
     */
    public function loadAllExcludedWebsites(): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getMainTable(),
            ['seller_group_id', 'website_id']
        );

        return $connection->fetchAll($select);
    }

    /**
     * Delete seller group with its excluded websites.
     *
     * @param int $sellerGroupId
     * @return GroupExcludedWebsite
     * @throws LocalizedException
     */
    public function delete($sellerGroupId)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $where = $connection->quoteInto('seller_group_id = ?', $sellerGroupId);
            $connection->delete(
                $this->getMainTable(),
                $where
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Delete seller group excluded website by id.
     *
     * @param int $websiteId
     * @return int
     * @throws LocalizedException
     */
    public function deleteByWebsite(int $websiteId): int
    {
        return $this->getConnection()->delete($this->getMainTable(), ['website_id = ?' => $websiteId]);
    }
}
