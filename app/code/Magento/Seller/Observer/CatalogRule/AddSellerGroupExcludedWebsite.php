<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Observer\CatalogRule;

use Magento\Seller\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded seller group websites to catalog rule.
 */
class AddSellerGroupExcludedWebsite implements ObserverInterface
{
    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $sellerGroupExcludedWebsiteRepository;

    /**
     * @param GroupExcludedWebsiteRepositoryInterface $sellerGroupExcludedWebsiteRepository
     */
    public function __construct(
        GroupExcludedWebsiteRepositoryInterface $sellerGroupExcludedWebsiteRepository
    ) {
        $this->sellerGroupExcludedWebsiteRepository = $sellerGroupExcludedWebsiteRepository;
    }

    /**
     * Add excluded seller group websites to catalog rule as extension attributes.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $catalogRule = $observer->getData('catalog_rule');
        $rules = $catalogRule->getItems();
        if (!empty($rules)) {
            $allExcludedWebsiteIds = $this->sellerGroupExcludedWebsiteRepository->getAllExcludedWebsites();
            if (!empty($allExcludedWebsiteIds)) {
                foreach ($rules as $rule) {
                    if ($rule->getIsActive()) {
                        $excludedWebsites = [];
                        $sellerGroupIds = $rule->getSellerGroupIds();
                        if (!empty($sellerGroupIds)) {
                            foreach ($sellerGroupIds as $sellerGroupId) {
                                if (array_key_exists((int)$sellerGroupId, $allExcludedWebsiteIds)) {
                                    $excludedWebsites[$sellerGroupId] = $allExcludedWebsiteIds[(int)$sellerGroupId];
                                }
                            }
                            if (!empty($excludedWebsites)) {
                                $ruleExtensionAttributes = $rule->getExtensionAttributes();
                                $ruleExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
                                $rule->setExtensionAttributes($ruleExtensionAttributes);
                            }
                        }
                    }
                }
            }
        }
    }
}
