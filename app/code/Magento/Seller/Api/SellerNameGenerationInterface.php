<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api;

use Magento\Seller\Api\Data\SellerInterface;

/**
 * Interface SellerNameGenerationInterface
 *
 * @api
 * @since 100.1.0
 */
interface SellerNameGenerationInterface
{
    /**
     * Concatenate all seller name parts into full seller name.
     *
     * @param SellerInterface $sellerData
     * @return string
     * @since 100.1.0
     */
    public function getSellerName(SellerInterface $sellerData);
}
