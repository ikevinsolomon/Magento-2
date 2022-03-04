<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Indexer;

use Magento\Seller\Model\Seller;

/**
 * Seller indexer
 */
class Processor extends \Magento\Framework\Indexer\AbstractProcessor
{
    const INDEXER_ID = Seller::SELLER_GRID_INDEXER_ID;
}
