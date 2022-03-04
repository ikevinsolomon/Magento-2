<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Seller\Model\Session as SellerSession;

/**
 * Session-based seller user context
 */
class SellerSessionUserContext implements UserContextInterface
{
    /**
     * @var SellerSession
     */
    protected $_sellerSession;

    /**
     * Initialize dependencies.
     *
     * @param SellerSession $sellerSession
     */
    public function __construct(
        SellerSession $sellerSession
    ) {
        $this->_sellerSession = $sellerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->_sellerSession->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_SELLER;
    }
}
