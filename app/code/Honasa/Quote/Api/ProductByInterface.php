<?php

namespace Honasa\Quote\Api;

interface ProductByInterface
{
    /**
     * GET product by its ID
     *
     * @api
     * @param string $id
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductById($id);

    /**
     * get subcategory.
     *
     * @api
     * @param int $id
     * @return $this
     */
    public function subcategory($id);

}
