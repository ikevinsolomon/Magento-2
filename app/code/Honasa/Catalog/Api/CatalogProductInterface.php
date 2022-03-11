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
     * @param int $pageNumber
     * @param int $pageSize
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
     * Get product by slug
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsBySlug($productSlug);


    /**
     * Get list of upsell products by slug
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUpSellProductsBySlug($productSlug);


    /**
     * Get list of crosssell products by slug
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCrossSellProductsBySlug($productSlug);


    /**
     * Get list of related products by slug
     * @param string $productSlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRelatedProductsBySlug($productSlug);


    /**
     * Get list of products by category Ids
     * @api
     * @param string $categoryIds
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryDetailsById($categoryIds);
}
