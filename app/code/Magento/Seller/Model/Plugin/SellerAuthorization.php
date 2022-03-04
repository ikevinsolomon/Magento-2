<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\Plugin;

use Closure;
use Magento\Seller\Model\Seller\AuthorizationComposite;
use Magento\Framework\Authorization;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Plugin to allow seller users to access resources with self permission
 */
class SellerAuthorization
{
    /**
     * @var AuthorizationComposite
     */
    private $authorizationComposite;

    /**
     * Inject dependencies.
     * @param AuthorizationComposite $composite
     */
    public function __construct(
        AuthorizationComposite $composite
    ) {
        $this->authorizationComposite = $composite;
    }

    /**
     * Verify if to allow seller users to access resources with self permission
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param Authorization $subject
     * @param Closure $proceed
     * @param string $resource
     * @param mixed $privilege
     * @return bool
     */
    public function aroundIsAllowed(
        Authorization $subject,
        Closure $proceed,
        string $resource,
        $privilege = null
    ) {
        if ($this->authorizationComposite->isAllowed($resource, $privilege)) {
            return true;
        }

        return $proceed($resource, $privilege);
    }
}
