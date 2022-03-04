<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Seller\Model\Plugin;

use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Website;

/**
 * Run seller_grid indexer after deleting website for specified sellers
 */
class SellerGridIndexAfterWebsiteDelete
{
    private const SELLER_GRID_INDEXER_ID = 'seller_grid';

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var SellerCollectionFactory
     */
    private $sellerCollectionFactory;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param SellerCollectionFactory $sellerCollectionFactory
     */
    public function __construct(IndexerRegistry $indexerRegistry, SellerCollectionFactory $sellerCollectionFactory)
    {
        $this->indexerRegistry = $indexerRegistry;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
    }

    /**
     * Run seller_grid indexer after deleting website
     *
     * @param Website $subject
     * @param callable $proceed
     * @return Website
     */
    public function aroundDelete(Website $subject, callable $proceed): Website
    {
        $sellerIds = $this->getSellerIdsByWebsiteId((int) $subject->getId());
        $result = $proceed();

        if ($sellerIds) {
            $this->indexerRegistry->get(self::SELLER_GRID_INDEXER_ID)
                ->reindexList($sellerIds);
        }

        return $result;
    }

    /**
     * Returns seller ids by website id
     *
     * @param int $websiteId
     * @return array
     */
    private function getSellerIdsByWebsiteId(int $websiteId): array
    {
        $collection = $this->sellerCollectionFactory->create();
        $collection->addFieldToFilter('website_id', $websiteId);

        return $collection->getAllIds();
    }
}
