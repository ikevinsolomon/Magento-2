<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api\Data;

/**
 * Interface for seller groups search results.
 * @api
 * @since 100.0.2
 */
interface GroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get seller groups list.
     *
     * @return \Magento\Seller\Api\Data\GroupInterface[]
     */
    public function getItems();

    /**
     * Set seller groups list.
     *
     * @api
     * @param \Magento\Seller\Api\Data\GroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
