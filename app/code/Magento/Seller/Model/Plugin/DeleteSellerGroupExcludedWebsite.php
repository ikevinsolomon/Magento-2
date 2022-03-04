<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Seller\Model\ResourceModel\GroupExcludedWebsiteRepository;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Delete seller group excluded websites while deleting seller group by id.
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
     * Delete excluded seller group websites while deleting seller group by id.
     *
     * @param GroupRepositoryInterface $subject
     * @param bool $result
     * @param string $groupId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, bool $result, string $groupId): bool
    {
        $excludedWebsites = $this->groupExcludedWebsiteRepository->getSellerGroupExcludedWebsites((int)$groupId);
        if (!empty($excludedWebsites)) {
            try {
                $this->groupExcludedWebsiteRepository->delete((int)$groupId);
            } catch (LocalizedException $e) {
                throw new CouldNotDeleteException(
                    __(
                        'Could not delete seller group website with ID: %1',
                        $groupId
                    ),
                    $e
                );
            }
            // invalidate product price index if websites were deleted from seller group exclusion
            $priceIndexer = $this->priceIndexProcessor->getIndexer();
            $priceIndexer->invalidate();
        }

        return $result;
    }
}
