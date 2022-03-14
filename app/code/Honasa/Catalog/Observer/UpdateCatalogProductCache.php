<?php

namespace Honasa\Catalog\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class UpdateCatalogProductCache implements ObserverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;


    public function __construct(
        \Magento\Catalog\Model\Product                                  $product,
        \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface                      $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder                    $searchCriteriaBuilder,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository       $stockItemRepository

    )
    {
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->stockItemRepository = $stockItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(Observer $observer)
    {
    }
}
