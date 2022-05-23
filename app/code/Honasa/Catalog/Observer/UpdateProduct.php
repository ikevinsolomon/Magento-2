<?php

namespace Honasa\Catalog\Observer;

use Honasa\Base\Helper\CatalogHelper;
use Honasa\Base\Helper\CatalogEventHelper;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;


class UpdateProduct implements ObserverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    const CATALOG_PRODUCT_RESOURCE = 'Catalog_Product';


    public function __construct(
        CatalogHelper $catalogHelper,
        CatalogEventHelper $catalogEventHelper
    )
    {
        $this->catalogHelper = $catalogHelper;
        $this->catalogEventHelper = $catalogEventHelper;
    }

    public function execute(Observer $observer)
    { 
        try {
            $product = $observer->getProduct();
            $eventData = [];
            $eventData['event_name'] = self::CATALOG_PRODUCT_RESOURCE;
            $eventData['products'] = $this->catalogHelper->transformProduct([$product]);
            $this->catalogEventHelper->sendToCatalogQueue($eventData);

        } catch (Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('LOGGING EXCEPTION', ['error' => $e]);
        }
    }
}
