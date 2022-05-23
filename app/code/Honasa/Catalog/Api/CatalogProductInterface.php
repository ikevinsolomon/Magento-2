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
     * Get list of upsell products by id
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUpSellProductsById($productId);

    /**
     * Get list of crosssell products by id
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCrossSellProductsById($productId);

    /**
     * Get list of related products by id
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRelatedProductsById($productId);


    /**
     * Retrieve list of categories
     *
     * @param int $rootCategoryId
     * @param int $depth
     * @throws \Magento\Framework\Exception\NoSuchEntityException If ID is not found
     * @return \Honasa\Catalog\Api\Data\CategoryTreeInterface containing Tree objects
     */
    public function getCategories();


    /**
     * Get list of products by category Ids
     * @api
     * @param int $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsByCategoryId($categoryId);

    /**
     * Get list of products by category Ids
     * @api
     * @param string $categorySlug
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsByCategorySlug($categorySlug);

    /**
     * Get positions of product in a category id
     * @api
     * @param int $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductsPosition($categoryId);
}
