<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\ResourceModel\Group;

use Magento\Framework\App\ResourceConnection;

/**
 * Resource model for seller group resolver service
 */
class Resolver
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Resolve seller group from db
     *
     * @param int $sellerId
     * @return int|null
     */
    public function resolve(int $sellerId) : ?int
    {
        $result = null;

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('seller_entity');

        $query = $connection
            ->select()
            ->from(
                ['main_table' => $tableName],
                ['main_table.group_id']
            )
            ->where('main_table.entity_id = ?', $sellerId);
        $groupId = $connection->fetchOne($query);
        if ($groupId) {
            $result = (int) $groupId;
        }

        return $result;
    }
}
