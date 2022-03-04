<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api;

/**
 * Seller CRUD interface.
 * @api
 * @since 100.0.2
 */
interface SellerRepositoryInterface
{
    /**
     * Create or update a seller.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @param string $passwordHash
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Seller\Api\Data\SellerInterface $seller, $passwordHash = null);

    /**
     * Retrieve seller.
     *
     * @param string $email
     * @param int|null $websiteId
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email, $websiteId = null);

    /**
     * Get seller by Seller ID.
     *
     * @param int $sellerId
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($sellerId);

    /**
     * Retrieve sellers which match a specified criteria.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See https://devdocs.magento.com/codelinks/attributes.html#SellerRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Seller\Api\Data\SellerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete seller.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Seller\Api\Data\SellerInterface $seller);

    /**
     * Delete seller by Seller ID.
     *
     * @param int $sellerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sellerId);
}
