<?php

namespace Honasa\Quote\Api\Data;

/**
 * @api
 */
interface CartdataInterface
{
    /**
     * Get Id
     *
     * @return int
     */
    public function getId();
    /**
     * Get Id
     *
     * @return int $id
     */
    public function setId($id);
    /**
     * Get createat
     *
     * @return string
     */
    public function getCreatedAt();
    /**
     * Get name
     *
     * @return string $createat
     */
    public function setCreatedAt($createat);
    /**
     * Get updatedat
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Get name
     *
     * @return string $updatedat
     */
    public function setUpdatedAt($updatedat);
    /**
     * Get isactive
     *
     * @return string
     */
    public function getIsActive();

    /**
     * Get name
     *
     * @return string $isactive
     */
    public function setIsActive($isactive);

    /**
     * @return array
     */
    public function getItems();
    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items);


    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     */
    public function getTotals();
    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals.
     * @return $this
     */
    public function setTotals(\Magento\Quote\Api\Data\TotalsInterface $totals = null);

}

