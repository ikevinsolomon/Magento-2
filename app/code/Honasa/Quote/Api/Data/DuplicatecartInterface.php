<?php

namespace Honasa\Quote\Api\Data;

/**
 * @api
 */
interface DuplicatecartInterface
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
     * @return array
     */
    public function getItems();
    /**

     * @param array $items
     * @return $this
     */
    public function setItems(array $items);

}

