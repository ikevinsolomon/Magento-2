<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Seller\Api\Data\GroupInterface;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Seller\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Seller\Model\Data\GroupExcludedWebsiteFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Save seller group websites excluded for certain seller group.
 */
class SaveSellerGroupExcludedWebsite
{
    /**
     * @var GroupExcludedWebsiteFactory
     */
    private $groupExcludedWebsiteFactory;

    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @param GroupExcludedWebsiteFactory $groupExcludedWebsiteFactory
     * @param GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository
     * @param SystemStore $systemStore
     * @param Processor $priceIndexProcessor
     */
    public function __construct(
        GroupExcludedWebsiteFactory $groupExcludedWebsiteFactory,
        GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository,
        SystemStore $systemStore,
        Processor $priceIndexProcessor
    ) {
        $this->groupExcludedWebsiteFactory = $groupExcludedWebsiteFactory;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
        $this->systemStore = $systemStore;
        $this->priceIndexProcessor = $priceIndexProcessor;
    }

    /**
     * Save excluded websites for seller group.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $result
     * @param GroupInterface $group
     * @return GroupInterface
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        GroupRepositoryInterface $subject,
        GroupInterface $result,
        GroupInterface $group
    ): GroupInterface {
        if ($result->getExtensionAttributes() && $result->getExtensionAttributes()->getExcludeWebsiteIds() !== null) {
            $websitesToExclude = array_intersect(
                $this->getAllWebsites(),
                $result->getExtensionAttributes()->getExcludeWebsiteIds()
            );
            $sellerGroupId = (int)$result->getId();

            // prevent NOT LOGGED IN sellers with id 0 to have excluded websites
            if ($sellerGroupId !== null && $sellerGroupId !== 0) {
                $excludedWebsites = $this->groupExcludedWebsiteRepository
                    ->getSellerGroupExcludedWebsites($sellerGroupId);
                $isValueChanged = $this->isValueChanged($excludedWebsites, $websitesToExclude);
                if ($isValueChanged) {
                    $this->groupExcludedWebsiteRepository->delete($sellerGroupId);
                    foreach ($websitesToExclude as $websiteToExclude) {
                        $groupExcludedWebsite = $this->groupExcludedWebsiteFactory->create();
                        $groupExcludedWebsite->setGroupId($sellerGroupId);
                        $groupExcludedWebsite->setExcludedWebsiteId((int)$websiteToExclude);
                        try {
                            $this->groupExcludedWebsiteRepository->save($groupExcludedWebsite);
                        } catch (LocalizedException $e) {
                            throw new CouldNotSaveException(
                                __(
                                    'Could not save seller group website to exclude with ID: %1',
                                    $websiteToExclude
                                ),
                                $e
                            );
                        }
                    }
                    // invalidate product price index if new websites are excluded from seller group
                    $priceIndexer = $this->priceIndexProcessor->getIndexer();
                    $priceIndexer->invalidate();
                }
            }
        }

        return $result;
    }

    /**
     * Get all websites.
     *
     * @return array
     */
    private function getAllWebsites(): array
    {
        $websiteCollection = $this->systemStore->getWebsiteCollection();

        $websites = [];
        foreach ($websiteCollection as $website) {
            $websites[] = (int)$website->getWebsiteId();
        }

        return $websites;
    }

    /**
     * Check if there are new websites to exclude from the seller group.
     *
     * @param array $currentValues
     * @param array $newValues
     * @return bool
     */
    private function isValueChanged(array $currentValues, array $newValues): bool
    {
        return !($currentValues === array_intersect($currentValues, $newValues)
            && $newValues === array_intersect($newValues, $currentValues));
    }

}
