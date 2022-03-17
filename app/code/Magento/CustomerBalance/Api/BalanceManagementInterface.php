<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerBalance\Api;

/**
 * Customer balance(store credit) operations
 * @api
 */
interface BalanceManagementInterface
{
    /**
     * Apply store credit
     *
     * @param int $cartId
     * @return bool
     */
    public function apply($cartId);


    /**
     * remove store credit
     *
     * @param int $cartId
     * @return bool
     */

    public function remove($cartId);
	
	 /**
     * Apply store credit
     *
     * @param int $customer_id
     * @return \Magento\CustomerBalance\Api\Data\BalanceInterface
     */
    public function storecredit($customer_id);
    /**
     * customer balance expire
     *
     * @param int $customer_id
     * @return boolean true/false
     */
    public function balanceExpire($customer_id);

}
