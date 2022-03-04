<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Plugin;

use Magento\Seller\Api\Data\GroupInterface;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Seller\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Add excluded websites to seller group as extension attributes while retrieving this group by id.
 */
class GetByIdSellerGroupExcludedWebsite
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
     * Add excluded websites as extension attributes while getting seller group by id.
     *
     * @param GroupRepositoryInterface $subject
     * @param GroupInterface $result
     * @param int $id
     * @return GroupInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetById(
        GroupRepositoryInterface $subject,
        GroupInterface $result,
        int $id
    ): GroupInterface {
        $excludedWebsites = $this->groupExcludedWebsiteRepository->getSellerGroupExcludedWebsites($id);
        if (!empty($excludedWebsites)) {
            $sellerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
            $sellerGroupExtensionAttributes->setExcludeWebsiteIds($excludedWebsites);
            $result->setExtensionAttributes($sellerGroupExtensionAttributes);
        }

        return $result;
    }
}
