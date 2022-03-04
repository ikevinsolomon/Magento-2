<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Group;

/**
 * Interface for getting current seller group from session.
 *
 * @api
 * @since 101.0.0
 */
interface RetrieverInterface
{
    /**
     * Retrieve seller group id.
     *
     * @return int
     * @since 101.0.0
     */
    public function getSellerGroupId();
}
