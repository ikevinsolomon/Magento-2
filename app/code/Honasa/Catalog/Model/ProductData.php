<?php

namespace Honasa\Catalog\Model;

use Honasa\Catalog\Api\Data\ProductInterfaceFactory;
use Honasa\Catalog\Api\ProductDataInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Swatches\Helper\Data;

/**
 * Defines the implementaiton class of the calculator service contract.
 */
class ProductData implements ProductDataInterface
{
    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;
    protected $timezone;

    public function __construct(
        StoreManagerInterface          $storeManager,
        FormKey                        $formkey,
        \Magento\Catalog\Model\Product $product,
        Context                        $context,
        ProductInterfaceFactory        $dataFactory,
        ProductRepositoryInterface     $productRepository,
        ProductFactory                 $productFactory,
        TimezoneInterface              $timezone,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        Data                           $swatchHelper,
    )
    {
        $this->_storeManager = $storeManager;
        $this->_formkey = $formkey;
        $this->product = $product;
        $this->dataFactory = $dataFactory;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->timezone = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->swatchHelper = $swatchHelper;
    }

    public function getProducts()
    {
        // TODO: Implement getProducts() method.
    }

    public function getProductDetailsById($productId)
    {
        // TODO: Implement getProductDetailsById() method.
    }

    public function getProductDetailsBySlug($productSlug)
    {
        // TODO: Implement getProductDetailsBySlug() method.
    }

    public function getCrossSellProductsForProductById($productId)
    {
        // TODO: Implement getCrossProductsForProductById() method.
    }

    public function getUpSellProductsForProductById($productId)
    {
        // TODO: Implement getUpsellProductsForProductById() method.
    }

    public function getRelatedProductsForProductById($productId)
    {
        // TODO: Implement getRelatedProductsForProductById() method.
    }

    public function getProductsByCategoryId($categoryId)
    {
        // TODO: Implement getProductsByCategoryId() method.
    }

    public function getProductsByCategorySlug($categorySlug)
    {
        // TODO: Implement getProductsByCategorySlug() method.
    }

}


