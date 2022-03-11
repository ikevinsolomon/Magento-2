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
    protected $productCollectionFactory;
    protected $productRepository;
    protected $product;

    public function __construct(
        \Magento\Catalog\Model\Product                                 $product,
        \Magento\Catalog\Api\ProductRepositoryInterface                $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder                   $searchCriteriaBuilder,
        array                                                          $data = []
    )
    {
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
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
        if (isset($products) && count($products) > 0) {
            foreach ($products as $product) {
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$product->getId()', ['$product->getId()' => $product->getId()]);
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('$product->getName()', ['$product->getName()' => $product->getName()]);
                $result[] = [
                    'id' => $product->getId(),
                    'type' => $product->getTypeId(),
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'url_key' => $product->getUrlKey(),
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
            $products = $this->productCollectionFactory->create();
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

    public function getProductsByPage($pageNumber, $pageSize)
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];

        try {
            $products = $this->productCollectionFactory->create();
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
            $products = $this->productCollectionFactory->create();
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
            'message' => 'No Product Found with url key',
            'data' => []
        ];
        try {
            $products = $this->productCollectionFactory->create();
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

    public function getProductsByCategoryId($categoryIds)
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addCategoriesFilter(['in' => $categoryIds]);
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
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $upSellProducts = $products->getFirstItem()->getUpSellProductCollection()->addAttributeToSelect('*');
            $upSellProducts = $this->transformProduct($upSellProducts);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $upSellProducts
            ];
        } catch (Exception $e) {
            return $response;
        }
    }

    public function getCrossSellProductsBySlug($productSlug)
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $crossSell = $products->getFirstItem()->getCrossSellProductCollection()->addAttributeToSelect('*');
            $crossSell = $this->transformProduct($crossSell);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $crossSell
            ];
        } catch (Exception $e) {
            return $response;
        }
    }

    public function getRelatedProductsBySlug($productSlug)
    {
        $response = [
            'status' => 200,
            'resource' => self::CATALOG_PRODUCT_RESOURCE,
            'message' => 'No Products Found',
            'data' => []
        ];
        try {
            $products = $this->productCollectionFactory->create();
            $products->addAttributeToSelect('*')->addAttributeToFilter(
                [
                    ['attribute' => 'url_key', 'eq' => $productSlug],
                ]
            );
            $relatedProducts = $products->getFirstItem()->getRelatedProductCollection()->addAttributeToSelect('*');
            $relatedProducts = $this->transformProduct($relatedProducts);
            return [
                'status' => 200,
                'resource' => self::CATALOG_PRODUCT_RESOURCE,
                'message' => 'success',
                'data' => $relatedProducts
            ];
        } catch (Exception $e) {
            return $response;
        }
    }
}
