<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api;

/**
 * Seller group CRUD interface
 * @api
 * @since 100.0.2
 */
interface GroupRepositoryInterface
{
    /**
     * Save seller group.
     *
     * @param \Magento\Seller\Api\Data\GroupInterface $group
     * @return \Magento\Seller\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a group ID is sent but the group does not exist
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     *      If saving seller group with seller group code that is used by an existing seller group
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Seller\Api\Data\GroupInterface $group);

    /**
     * Get seller group by group ID.
     *
     * @param int $id
     * @return \Magento\Seller\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $groupId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve seller groups.
     *
     * The list of groups can be filtered to exclude the NOT_LOGGED_IN group using the first parameter and/or it can
     * be filtered by tax class.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#GroupRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Seller\Api\Data\GroupSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete seller group.
     *
     * @param \Magento\Seller\Api\Data\GroupInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If seller group cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Seller\Api\Data\GroupInterface $group);

    /**
     * Delete seller group by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If seller group cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
