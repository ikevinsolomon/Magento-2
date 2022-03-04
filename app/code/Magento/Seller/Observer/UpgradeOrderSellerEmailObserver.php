<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Seller\Model\Data\Seller;

/**
 * Class observer UpgradeOrderSellerEmailObserver
 * Update orders seller email after corresponding seller email changed
 */
class UpgradeOrderSellerEmailObserver implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Upgrade order seller email when seller has changed email
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Seller $originalSeller */
        $originalSeller = $observer->getEvent()->getOrigSellerDataObject();
        if (!$originalSeller) {
            return;
        }

        /** @var Seller $seller */
        $seller = $observer->getEvent()->getSellerDataObject();
        $sellerEmail = $seller->getEmail();

        if ($sellerEmail === $originalSeller->getEmail()) {
            return;
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::SELLER_ID, $seller->getId())
            ->create();

        /**
         * @var Collection $orders
         */
        $orders = $this->orderRepository->getList($searchCriteria);
        $orders->setDataToAll(OrderInterface::SELLER_EMAIL, $sellerEmail);
        $orders->save();
    }
}
