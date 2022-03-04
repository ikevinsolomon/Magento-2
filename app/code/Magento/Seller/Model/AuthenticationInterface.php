<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Interface \Magento\Seller\Model\AuthenticationInterface
 * @api
 * @since 100.1.0
 */
interface AuthenticationInterface
{
    /**
     * Process seller authentication failure
     *
     * @param int $sellerId
     * @return void
     * @since 100.1.0
     */
    public function processAuthenticationFailure($sellerId);

    /**
     * Unlock seller
     *
     * @param int $sellerId
     * @return void
     * @since 100.1.0
     */
    public function unlock($sellerId);

    /**
     * Check if a seller is locked
     *
     * @param int $sellerId
     * @return boolean
     * @since 100.1.0
     */
    public function isLocked($sellerId);

    /**
     * Authenticate seller
     *
     * @param int $sellerId
     * @param string $password
     * @return boolean
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     * @since 100.1.0
     */
    public function authenticate($sellerId, $password);
}
