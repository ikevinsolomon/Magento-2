<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardAccount\Api;

/**
 * Interface RedeemInterface
 * @api
 */
interface RedeemInterface
{
    /**
     * Return data object for specified GiftCard Account id
     *
     * @param string $giftcard_code
	 * @param int $customer_id
     * @return \Magento\GiftCardAccount\Api\RedeemInterface
     */
    public function redeem($giftcard_code,$customer_id);

   
}
