<?php

namespace Honasa\Customer\Api;

interface CustomerEntityInterface
{

    /**
     * Create Customer
     * @api
     * @param mixed $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function registerCustomer($data);

    /**
     * Get customer details by Id
     * @param int $customerId
     * @param int $pageSize
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerDetailsById($customerId);

    /**
     * Get customer order details by customer Id
     * @api
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerOrderDetailsById($customerId);

    /**
     * Get customer addresses by customer Id
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAddresses($customerId);

    /**
     * Get wallet balance by customer Id
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerWalletBalance($customerId);

    /**
     * Get wallet balance by customer Id
     * @param int $customerId
     * @param mixed $address
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setCustomerAddress($customerId, $address);



    /**
     * Get customer details by Id
     * @param int $customerId
     * @param mixed $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setCustomerDetailsById($customerId, $data);


}
