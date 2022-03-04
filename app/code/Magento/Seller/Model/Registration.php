<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

/**
 * @api
 * @since 100.0.2
 */
class Registration
{
    /**
     * Check whether sellers registration is allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        return true;
    }
}
