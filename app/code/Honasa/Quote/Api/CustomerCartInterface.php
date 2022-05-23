<?php

namespace Honasa\Quote\Api;

use Honasa\Quote\Api\Data\CartdataInterface;

interface CustomerCartInterface
{
    /**
     * Get customer by customer ID.
     *
     * @param int $customerId
     * @return \Honasa\Quote\Api\Data\CartdataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCartDetails($customerId);

    /**
     * create address.
     *
     * @api
     * @param int $customer_id
     * @return $this
     */

    public function createQuote($customer_id);
    /**
     * create address.
     *
     * @api
     * @param int $quote_id
     * @param int $customer_id
     * @return \Honasa\Quote\Api\Data\DuplicatecartInterface
     */

    public function duplicateQuote($quote_id, $customer_id);
    /**
     * create address.
     *
     * @api
     * @param string $quote_id
     * @return \Honasa\Quote\Api\Data\DuplicatecartInterface
     */

    public function duplicateQuoteGuest($quote_id);
    /**
     * merge cart.
     *
     * @api
     * @param mixed $data
     * @return $this
     */

    public function mergeCart($data);
    /**
     * Get guest cart details by quote id.
     *
     * @param string $quote_id
     * @return \Honasa\Quote\Api\Data\CartdataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGuestCartDetails($quote_id);


    /**
     * Get cart details by quote id.
     *
     * @param string $quote_id
     * @return \Honasa\Quote\Api\Data\CartdataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cartById($quote_id);
}
