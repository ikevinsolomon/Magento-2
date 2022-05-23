<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Honasa\Catalog\Api\Data;

/**
 * @api
 */
interface CategoryTreeInterface
{
    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get parent category ID
     *
     * @return int
     */
    public function getParentId();

    /**
     * Set parent category ID
     *
     * @param int $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * Get category name
     *
     * @return string
     */
    public function getName();

    /**
     * Set category name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get category image
     *
     * @return string
     */
    public function getImage();

    /**
     * Set category image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image);
    /**
     * Get category description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set category description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get category url_key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Set category url_key
     *
     * @param string $urlkey
     * @return $this
     */
    public function setUrlKey($urlkey);

    /**
     * Check whether category is active
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();

    /**
     * Set whether category is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get category position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set category position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get category level
     *
     * @return int
     */
    public function getLevel();

    /**
     * Set category level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level);

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductCount();

    /**
     * Set product count
     *
     * @param int $productCount
     * @return $this
     */
    public function setProductCount($productCount);

    /**
     * @return \Honasa\Catalog\Api\Data\CategoryTreeInterface[]
     */
    public function getChildrenData();

    /**
     * @param \Honasa\Catalog\Api\Data\CategoryTreeInterface[] $childrenData
     * @return $this
     */
    public function setChildrenData(array $childrenData = null);

    /**
     * Get category meta_description
     *
     * @return string
     */
    public function getMetaDescription();

    /**
     * Set category meta_description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get category meta_title
     *
     * @return string
     */
    public function getMetaTitle();

    /**
     * Set category meta_title
     *
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get category page_type
     *
     * @return int
     */
    public function getPageType();

    /**
     * Set product page_type
     *
     * @param int $pageType
     * @return $this
     */
    public function setPageType($pageType);
    
    /**
     * Get category meta_keywords
     *
     * @return string
     */
    public function getMetaKeywords();

    /**
     * Set category meta_keywords
     *
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords($metaKeywords);
}