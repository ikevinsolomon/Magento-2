<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Plugin;

use Magento\Seller\Api\Data\GroupSearchResultsInterface;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Seller\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded websites to seller groups as extension attributes while retrieving the list of all groups.
 */
class GetListSellerGroupExcludedWebsite
{
    /**
     * @var \Magento\Seller\Api\Data\GroupExtensionInterfaceFactory
     */
    private $groupExtensionInterfaceFactory;

    /**
     * @var GroupExcludedWebsiteRepository
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @param \Magento\Seller\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory
     * @param GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
     */
    public function __construct(
        \Magento\Seller\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory,
        GroupExcludedWebsiteRepository $groupExcludedWebsiteRepository
    ) {
        $this->groupExtensionInterfaceFactory = $groupExtensionInterfaceFactory;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
    }

    /**
     * Add excluded websites to seller groups as extension attributes while retrieving the list of all groups.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupSearchResultsInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     * @return GroupSearchResultsInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetList(
        GroupRepositoryInterface $subject,
        GroupSearchResultsInterface $result,
        SearchCriteriaInterface $searchCriteria
    ): GroupSearchResultsInterface {
        $sellerGroups = $result->getItems();
        if (!empty($sellerGroups)) {
            $allExcludedWebsites = $this->groupExcludedWebsiteRepository->getAllExcludedWebsites();
            if (!empty($allExcludedWebsites)) {
                foreach ($sellerGroups as $sellerGroup) {
                    $sellerGroupId = (int)$sellerGroup->getId();
                    if (array_key_exists($sellerGroupId, $allExcludedWebsites)) {
                        $excludedWebsites = $allExcludedWebsites[$sellerGroupId];
                        $sellerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
                        $sellerGroupExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
                        $sellerGroup->setExtensionAttributes($sellerGroupExtensionAttributes);
                    }
                }
            }
        }

        return $result;
    }
}
