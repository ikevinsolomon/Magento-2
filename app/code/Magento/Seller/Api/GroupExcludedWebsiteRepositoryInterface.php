<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Api;

use Magento\Seller\Api\Data\GroupExcludedWebsiteInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Seller group website repository interface for websites that are excluded from seller group.
 * @api
 */
interface GroupExcludedWebsiteRepositoryInterface
{
    /**
     * Save seller group excluded website.
     *
     * @param GroupExcludedWebsiteInterface $groupExcludedWebsite
     * @return GroupExcludedWebsiteInterface
     * @throws LocalizedException
     */
    public function save(GroupExcludedWebsiteInterface $groupExcludedWebsite): GroupExcludedWebsiteInterface;

    /**
     * Retrieve seller group excluded websites by seller group id.
     *
     * @param int $sellerGroupId
     * @return string[]
     * @throws LocalizedException
     */
    public function getSellerGroupExcludedWebsites(int $sellerGroupId): array;

    /**
     * Retrieve all excluded seller group websites per seller groups.
     *
     * @return int[]
     * @throws LocalizedException
     */
    public function getAllExcludedWebsites(): array;

    /**
     * Delete seller group with its excluded websites.
     *
     * @param int $sellerGroupId
     * @return bool
     * @throws LocalizedException
     */
    public function delete(int $sellerGroupId): bool;

    /**
     * Delete seller group excluded website by id.
     *
     * @param int $websiteId
     * @return bool
     * @throws LocalizedException
     */
    public function deleteByWebsite(int $websiteId): bool;
}
