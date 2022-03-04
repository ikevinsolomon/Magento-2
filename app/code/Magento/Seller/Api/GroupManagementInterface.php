<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api;

/**
 * Interface for managing seller groups.
 * @api
 * @since 100.0.2
 */
interface GroupManagementInterface
{
    /**
     * Check if seller group can be deleted.
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($id);

    /**
     * Get default seller group.
     *
     * @param int $storeId
     * @return \Magento\Seller\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultGroup($storeId = null);

    /**
     * Get seller group representing sellers not logged in.
     *
     * @return \Magento\Seller\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNotLoggedInGroup();

    /**
     * Get all seller groups except group representing sellers not logged in.
     *
     * @return \Magento\Seller\Api\Data\GroupInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoggedInGroups();

    /**
     * Get seller group representing all sellers.
     *
     * @return \Magento\Seller\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllSellersGroup();
}
