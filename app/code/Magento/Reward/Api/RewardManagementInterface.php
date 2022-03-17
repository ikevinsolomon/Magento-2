<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reward\Api;

/**
 * Interface RewardManagementInterface
 * @api
 */
interface RewardManagementInterface
{
    /**
     * Set reward points to quote
     *
     * @param int $cartId
     * @return boolean
     */
    public function set($cartId);

    /**
     * Get orders cashback amount.
     * @param int $OrderId
     * @param string $userEmailHash
     * @return string
     * @throw LocalizedException 
     */
    public function getCashBack($OrderId,$userEmailHash);
}
