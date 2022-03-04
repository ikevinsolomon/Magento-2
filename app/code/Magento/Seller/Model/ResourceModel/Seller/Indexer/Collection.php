<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel\Seller\Indexer;

/**
 * Sellers collection for seller_grid indexer
 */
class Collection extends \Magento\Seller\Model\ResourceModel\Seller\Collection
{
    /**
     * @inheritdoc
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }
}
