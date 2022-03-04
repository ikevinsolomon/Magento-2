<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Plugin\Website;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Seller\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Website;

/**
 * Delete excluded seller group website after deleting the website.
 */
class DeleteSellerGroupExcludedWebsite
{
    /**
     * @var GroupExcludedWebsiteRepository
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @param GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
     * @param Processor $priceIndexProcessor
     */
    public function __construct(
        GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository,
        Processor $priceIndexProcessor
    ) {
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
        $this->priceIndexProcessor = $priceIndexProcessor;
    }

    /**
     * Delete excluded seller group website after deleting this website.
     *
     * @param Website $subject
     * @param Website $result
     * @return Website
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterDelete(
        Website $subject,
        Website $result
    ): Website {
        $websiteId = (int)$result->getId();
        if (!empty($websiteId)) {
            $deletedRecords = $this->groupExcludedWebsiteRepository->deleteByWebsite($websiteId);
            if ($deletedRecords) {
                // invalidate product price index if website was deleted from seller group exclusion
                $priceIndexer = $this->priceIndexProcessor->getIndexer();
                $priceIndexer->invalidate();
            }
        }

        return $result;
    }
}
