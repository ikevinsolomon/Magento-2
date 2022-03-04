<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel\Seller;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver;
use Magento\Seller\Model\Seller;

/**
 * @deprecated 100.1.0
 */
class Grid
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Framework\Indexer\ScopeResolver\FlatScopeResolver
     */
    protected $flatScopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexerRegistry $indexerRegistry
     * @param FlatScopeResolver $flatScopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexerRegistry $indexerRegistry,
        FlatScopeResolver $flatScopeResolver
    ) {
        $this->resource = $resource;
        $this->indexerRegistry = $indexerRegistry;
        $this->flatScopeResolver = $flatScopeResolver;
    }

    /**
     * Synchronize seller grid
     *
     * @return void
     *
     * @deprecated 100.1.0
     */
    public function syncSellerGrid()
    {
        $indexer = $this->indexerRegistry->get(Seller::SELLER_GRID_INDEXER_ID);
        $sellerIds = $this->getSellerIdsForReindex();
        if ($sellerIds) {
            $indexer->reindexList($sellerIds);
        }
    }

    /**
     * Retrieve seller IDs for reindex
     *
     * @return array
     *
     * @deprecated 100.1.0
     */
    protected function getSellerIdsForReindex()
    {
        $connection = $this->resource->getConnection();
        $gridTableName = $this->flatScopeResolver->resolve(Seller::SELLER_GRID_INDEXER_ID, []);

        $select = $connection->select()
            ->from($this->resource->getTableName($gridTableName), 'last_visit_at')
            ->order('last_visit_at DESC')
            ->limit(1);
        $lastVisitAt = $connection->query($select)->fetchColumn();

        $select = $connection->select()
            ->from($this->resource->getTableName('seller_log'), 'seller_id')
            ->where('last_login_at > ?', $lastVisitAt);

        $sellerIds = [];
        foreach ($connection->query($select)->fetchAll() as $row) {
            $sellerIds[] = $row['seller_id'];
        }

        return $sellerIds;
    }
}
