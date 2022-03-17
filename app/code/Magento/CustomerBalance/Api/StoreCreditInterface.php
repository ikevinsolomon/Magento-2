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
interface StoreCreditInterface
{
    /**
     * Apply store credit
     *
     * @param int $cartId
     * @return bool
     */
    public function apply($cartId);
	
	 /**
     * Apply store credit
     *
     * @param int $customer_id
     * @return \Magento\CustomerBalance\Api\Data\BalanceInterface
     */
    public function storecredit($customer_id);
	
}
