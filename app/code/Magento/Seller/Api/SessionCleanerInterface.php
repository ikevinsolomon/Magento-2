<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Api;

/**
 * Interface for cleaning seller session data.
 */
interface SessionCleanerInterface
{
    /**
     * Destroy all active seller sessions related to given seller except current session.
     *
     * @param int $sellerId
     * @return void
     */
    public function clearFor(int $sellerId): void;
}
