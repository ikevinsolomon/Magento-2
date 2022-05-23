<?php
namespace Honasa\Catalog\Observer;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Category;
use Psr\Log\LoggerInterface;
use Honasa\Base\Helper\CatalogEventHelper;

class UpdateCategory implements ObserverInterface {

    const CATALOG_CATEGORY_RESOURCE = 'Catalog_Category';
    public function __construct(
        LoggerInterface $logger, 
        Category $category,
        CatalogEventHelper $catalogHelper
        ) {
        $this->logger = $logger;
        $this->category = $category;
        $this->catalogHelper = $catalogHelper;
    }

    public function execute(Observer $observer) {
        try {
            // get category from event
            $category = $observer->getEvent()->getCategory();
            $categoryId = $category->getId();
            $eventData = [];
            $category = $this->category->load($categoryId);
            $productsPosition = $category->getProductsPosition();
            $eventData['category_id'] = $categoryId;
            $eventData['event_name'] = self::CATALOG_CATEGORY_RESOURCE;
            $eventData['products_position'] = $productsPosition;
            $this->logger->debug('LOGGING Event Data ----> ', ['eventData' => $eventData]);
            $this->catalogHelper->sendToCatalogQueue($eventData);
        }
        catch(Exception $e) {
            $this->logger->debug('LOGGING EXCEPTION', ['error' => $e]);
        }
    }
}