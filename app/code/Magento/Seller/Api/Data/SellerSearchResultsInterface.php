<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api\Data;

/**
 * Interface for seller search results.
 * @api
 * @since 100.0.2
 */
interface SellerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get sellers list.
     *
     * @return \Magento\Seller\Api\Data\SellerInterface[]
     */
    public function getItems();

    /**
     * Set sellers list.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
