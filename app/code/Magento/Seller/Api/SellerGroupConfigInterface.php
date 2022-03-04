<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api;

/**
 * Interface for system configuration operations for seller groups.
 *
 * @api
 * @since 101.0.0
 */
interface SellerGroupConfigInterface
{
    /**
     * Set system default seller group.
     *
     * @param int $id
     * @return int
     * @throws \UnexpectedValueException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 101.0.0
     */
    public function setDefaultSellerGroup($id);
}
