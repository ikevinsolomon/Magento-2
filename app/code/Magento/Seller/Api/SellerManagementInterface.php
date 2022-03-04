<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api;

/**
 * @api
 * @since 100.0.2
 */
interface SellerManagementInterface
{
    /**
     * Provide the number of seller count
     *
     * @return int
     */
    public function getCount();
}
