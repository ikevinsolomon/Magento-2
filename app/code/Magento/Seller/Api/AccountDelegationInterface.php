<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Api;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Delegating account actions from outside of seller module.
 */
interface AccountDelegationInterface
{
    /**
     * Create redirect to default new account form.
     *
     * @param SellerInterface $seller Pre-filled seller data.
     * @param array|null $mixedData Add this data to new-seller event
     * if the new seller is created.
     *
     * @return Redirect
     */
    public function createRedirectForNew(
        SellerInterface $seller,
        array $mixedData = null
    ): Redirect;
}
