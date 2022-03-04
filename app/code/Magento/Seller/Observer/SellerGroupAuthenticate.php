<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Observer;

use Magento\Seller\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Seller group authenticate observer.
 */
class SellerGroupAuthenticate implements ObserverInterface
{
    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $sellerGroupExcludedWebsiteRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GroupExcludedWebsiteRepositoryInterface $sellerGroupExcludedWebsiteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        GroupExcludedWebsiteRepositoryInterface $sellerGroupExcludedWebsiteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->sellerGroupExcludedWebsiteRepository = $sellerGroupExcludedWebsiteRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Do not authenticate seller if website is excluded from seller's group.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $seller = $observer->getData('model');
        if ($seller->getGroupId()) {
            $excludedWebsites = $this->sellerGroupExcludedWebsiteRepository->getSellerGroupExcludedWebsites(
                (int)$seller->getGroupId()
            );
            if (in_array($websiteId, $excludedWebsites, true)) {
                throw new LocalizedException(__('This website is excluded from seller\'s group.'));
            }
        }
    }
}
