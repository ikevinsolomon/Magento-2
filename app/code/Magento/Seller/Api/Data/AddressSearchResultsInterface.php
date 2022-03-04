<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api\Data;

/**
 * Interface for seller address search results.
 * @api
 * @since 100.0.2
 */
interface AddressSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get seller addresses list.
     *
     * @return \Magento\Seller\Api\Data\AddressInterface[]
     */
    public function getItems();

    /**
     * Set seller addresses list.
     *
     * @param \Magento\Seller\Api\Data\AddressInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
