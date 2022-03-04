<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api;

/**
 * Seller address CRUD interface.
 * @api
 * @since 100.0.2
 */
interface AddressRepositoryInterface
{
    /**
     * Save seller address.
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @return \Magento\Seller\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Seller\Api\Data\AddressInterface $address);

    /**
     * Retrieve seller address.
     *
     * @param int $addressId
     * @return \Magento\Seller\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($addressId);

    /**
     * Retrieve sellers addresses matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Seller\Api\Data\AddressSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete seller address.
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magento\Seller\Api\Data\AddressInterface $address);

    /**
     * Delete seller address by ID.
     *
     * @param int $addressId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($addressId);
}
