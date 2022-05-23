<?php

namespace Honasa\Catalog\Model;

use Exception;
use Honasa\Catalog\Api\CatalogProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Honasa\Base\Model\Data\ResponseFactory;
use Honasa\Base\Helper\CatalogHelper;
use Honasa\Catalog\Helper\Tree;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Category;


/**
 * Defines the implementaiton class of the calculator service contract.
 */
class CatalogProduct implements CatalogProductInterface
{
    const CATALOG_PRODUCT_RESOURCE = 'Catalog_Product';
    const CATALOG_CATEGORY_RESOURCE = 'Catalog_Category';

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;
    protected $productCollectionFactory;
    protected $productRepository;
    protected $product;

    public function __construct(
        Product $product,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface  $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockItemRepository $stockItemRepository,
        ResponseFactory $responseFactory,
        CatalogHelper $catalogHelper,
        Tree $tree,
        LoggerInterface $logger,
        Category $category
    ){
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->responseFactory = $responseFactory;
        $this->catalogHelper = $catalogHelper;
        $this->tree = $tree;
        $this->logger = $logger;
        $this->category = $category;
    }

    public function getProducts()
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);

        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;

        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getProductsByPage($pageNumber, $pageSize)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);

        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*');
            $products->setPageSize($pageSize);
            $products->setPageSize($pageSize);
            $products->setCurPage($pageNumber);
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array)$response;

        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getProductsById($productId)
    {

        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $product = $this->productRepository->getById($productId);
            $product = $this->catalogHelper->transformSingleProduct($product);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($product);
            return (array)$response;

        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getProductsBySlug($productSlug)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;

        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getUpSellProductsBySlug($productSlug)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $products = $products->getFirstItem()->getUpSellProductCollection()->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;
        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getCrossSellProductsBySlug($productSlug)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $products = $products->getFirstItem()->getCrossSellProductCollection()->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;
        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getRelatedProductsBySlug($productSlug)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $products = $products->getFirstItem()->getRelatedProductCollection()->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;
        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getUpSellProductsById($productId)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $product = $this->productCollectionFactory->create();
            $product->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'entity_id', 'eq' => $productId],
                ]
            );
            $products = $product->getFirstItem()->getUpSellProductCollection()->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;
        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getCrossSellProductsById($productId)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $product = $this->productCollectionFactory->create();
            $product->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'entity_id', 'eq' => $productId],
                ]
            );
            $products = $product->getFirstItem()->getCrossSellProductCollection()->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;
        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getRelatedProductsById($productId)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_PRODUCT_RESOURCE);
        try {
            $product = $this->productCollectionFactory->create();
            $product->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'entity_id', 'eq' => $productId],
                ]
            );
            $products = $product->getFirstItem()->getRelatedProductCollection()->addAttributeToSelect('*');
            $products = $this->catalogHelper->transformProduct($products);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($products);
            return (array) $response;
        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getCategories()
    {
        try {
        $treeData = $this->tree->getTree($this->tree->getRootNode());
        return $treeData;
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        }
        return null;
    }

    public function getProductsByCategoryId($categoryId)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_CATEGORY_RESOURCE);
        try {
            $categories = $this->categoryCollectionFactory->create();
            $categories->addAttributeToSelect('*')->addFieldToFilter('entity_id', array('in' => $categoryId));
            $categories->setStore($this->storeManager->getStore());
            $categories->addAttributeToFilter('is_active', '1');
            $categories = $this->catalogHelper->transformCategory($categories);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($categories);
            return (array) $response;

        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getProductsByCategorySlug($categorySlug)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_CATEGORY_RESOURCE);
        try {
            $categories = $this->categoryCollectionFactory->create();
            $categories->addAttributeToSelect('*')->addFieldToFilter( [
                ['attribute' => 'url_key', 'eq' => $categorySlug],
            ]);
            $categories->setStore($this->storeManager->getStore());
            $categories->addAttributeToFilter('is_active', '1');
            $categories = $this->catalogHelper->transformCategory($categories);
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($categories);

        } catch (Exception $e) {
            $response->setMessage($e->getMessage());
        }
        return $response;
    }

    public function getProductsPosition($categoryId){
        $response = $this->responseFactory->create();
        $response->setResource(self::CATALOG_CATEGORY_RESOURCE);
        try {
            $category = $this->category->load($categoryId);
            $positions = $category->getProductsPosition();
            $response->setStatus(true);
            $response->setMessage('success');
            $response->setData($positions);
            return (array) $response;
        } catch(Exception $e){
            $response->setMessage($e->getMessage());
        }
        return $response;
    }
}
