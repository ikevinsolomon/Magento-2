<?php

namespace Honasa\Catalog\Api;

interface CatalogProductInterface
{

    /**
     * Get list of products
     * @api*
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts();



    /**
     * Get list of products
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsByPage($pageNumber, $pageSize);



    /**
     * Get list of products
     * @api
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsById($productId);



    /**
     * Get list of products
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsBySlug($productSlug);


    /**
     * Get list of products
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUpSellProductsBySlug($productSlug);


    /**
     * Get list of products
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCrossSellProductsBySlug($productSlug);


    /**
     * Get list of products
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRelatedProductsBySlug($productSlug);
}
