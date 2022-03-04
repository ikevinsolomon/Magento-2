<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model;

use Magento\Seller\Api\Data\SellerInterface;

/**
 * Account Management service implementation for external API access.
 * Handle various seller account actions.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountManagementApi extends AccountManagement
{
    /**
     * @inheritDoc
     *
     * Override createAccount method to unset confirmation attribute for security purposes.
     */
    public function createAccount(SellerInterface $seller, $password = null, $redirectUrl = '')
    {
        $seller = parent::createAccount($seller, $password, $redirectUrl);
        $seller->setConfirmation(null);

        return $seller;
    }
}
