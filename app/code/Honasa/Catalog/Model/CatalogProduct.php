<?php

namespace Honasa\Catalog\Model;

use Exception;
use Honasa\Catalog\Api\CatalogProductInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;


/**
 * Defines the implementaiton class of the calculator service contract.
 */
class CatalogProduct implements CatalogProductInterface
{
    const CATALOG_PRODUCT_RESOURCE = 'Catalog_Product';
    const CATALOG_CATEGORY_RESOURCE = 'Catalog_Category';

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;
    protected $productFactory;
    protected $productRepository;
    protected $product;

    public function __construct(
        \Magento\Catalog\Model\Product                                 $product,
        \Magento\Catalog\Api\ProductRepositoryInterface                $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder                   $searchCriteriaBuilder,
        array                                                          $data = []
    )
    {
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /*
     * Note calling productRepository adds almost 2s to API from 340ms to 2.6s
     */
    public function getProductMediaGallery($productSku)
    {
        $mediaGallery = [];
        $mediaGalleryEntries = $this->productRepository->get($productSku)->getMediaGalleryEntries();
        foreach ($mediaGalleryEntries as $media) {
            $mediaGallery[] = $media->getData();
        }
        return $mediaGallery;
    }


    /*
     * Note calling productRepository adds almost 2s to API from 340ms to 2.6s
     */
    public function getProductCustomAttributes($productSku)
    {
        $customAttributes = [];
        $customAttributesEntries = $this->productRepository->get($productSku)->getCustomAttributes();
        foreach ($customAttributesEntries as $customAttribute) {
            $customAttributes[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
        }
        return $customAttributes;
    }

    /*
     * Note calling productRepository adds almost 2s to API from 340ms to 2.6s
     */
    public function getProductCategories($productSku)
    {
        $categories = [];
        $categoriesEntries = $this->productRepository->get($productSku)->getCategoryIds();
        foreach ($categoriesEntries as $categoriesEntry) {
            $categories[] = $categoriesEntry;
        }
        return $categories;
    }

    public function transformProduct($products)
    {
        $result = [];
        if (isset($products)) {
            foreach ($products as $product) {
                $result[] = [
                    'id' => $product->getId(),
                    'type' => $product->getTypeId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'slug' => $product->getUrlKey(),
                    'image' => $product->getImage(),
                    'small_image' => $product->getSmallImage(),
                    'thumbnail' => $product->getThumbnail(),
                    'price' => number_format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), 2, '.', ','),
                    'categories' => $product->getCategoryIds(),
                    'created_at' => $product->getCreatedAt(),
                    'updated_at' => $product->getUpdatedAt(),
                ];
            }
        }
        return $result;
    }

    public function getProducts()
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];

        try {
            $products = $this->productFactory->create();
            $products->addAttributeToSelect('*');
            $products = $this->transformProduct($products);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $products
            ];

        } catch (Exception $e) {
            return $response;
        }
    }


    public function getProductsByPage($pageNumber, $pageSize): array
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];

        try {
            $products = $this->productFactory->create();
            $products->addAttributeToSelect('*');
            $products->setPageSize($pageSize);
            $products->setPageSize($pageSize);
            $products->setCurPage($pageNumber);
            $products = $this->transformProduct($products);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $products
            ];

        } catch (Exception $e) {
            return $response;
        }
    }

    public function getProductsById($productId)
    {

        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];
        try {
            $products = $this->productFactory->create();
            $products->addAttributeToSelect('*')->addFieldToFilter('entity_id', array('in' => $productId));
            $products = $this->transformProduct($products);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $products
            ];

        } catch (Exception $e) {
            return $response;
        }
    }

    public function getProductsBySlug($productSlug)
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];
        try {
            $products = $this->productFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $products = $this->transformProduct($products);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $products
            ];

        } catch (Exception $e) {
            return $response;
        }
    }

    public function getUpSellProductsBySlug($productSlug)
    {
        // TODO: Implement getUpSellProductsBySlug() method.
    }

    public function getCrossSellProductsBySlug($productSlug)
    {
        // TODO: Implement getCrossSellProductsBySlug() method.
    }

    public function getRelatedProductsBySlug($productSlug)
    {
        // TODO: Implement getRelatedProductsBySlug() method.
    }
}
