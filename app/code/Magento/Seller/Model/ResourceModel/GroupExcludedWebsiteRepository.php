<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\ResourceModel;

use Magento\Seller\Api\Data\GroupExcludedWebsiteInterface;
use Magento\Seller\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Seller group website repository for CRUD operations with excluded websites.
 */
class GroupExcludedWebsiteRepository implements GroupExcludedWebsiteRepositoryInterface
{
    /**
     * @var GroupExcludedWebsite
     */
    private $groupExcludedWebsiteResourceModel;

    /**
     * @param GroupExcludedWebsite $groupExcludedWebsiteResourceModel
     */
    public function __construct(
        GroupExcludedWebsite $groupExcludedWebsiteResourceModel
    ) {
        $this->groupExcludedWebsiteResourceModel = $groupExcludedWebsiteResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function save(GroupExcludedWebsiteInterface $groupExcludedWebsite): GroupExcludedWebsiteInterface
    {
        try {
            $this->groupExcludedWebsiteResourceModel->save($groupExcludedWebsite);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save seller group website to exclude from seller group: "%1"', $e->getMessage())
            );
        }

        return $groupExcludedWebsite;
    }

    /**
     * @inheritdoc
     */
    public function getSellerGroupExcludedWebsites(int $sellerGroupId): array
    {
        try {
            return $this->groupExcludedWebsiteResourceModel->loadSellerGroupExcludedWebsites($sellerGroupId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not retrieve excluded seller group websites by seller group: "%1"', $e->getMessage())
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getAllExcludedWebsites(): array
    {
        try {
            $allExcludedWebsites = $this->groupExcludedWebsiteResourceModel->loadAllExcludedWebsites();
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not retrieve all excluded seller group websites.')
            );
        }

        $excludedWebsites = [];

        if (!empty($allExcludedWebsites)) {
            foreach ($allExcludedWebsites as $allExcludedWebsite) {
                $sellerGroupId = (int)$allExcludedWebsite['seller_group_id'];
                $websiteId = (int)$allExcludedWebsite['website_id'];
                $excludedWebsites[$sellerGroupId][] = $websiteId;
            }
        }

        return $excludedWebsites;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $sellerGroupId): bool
    {
        try {
            $this->groupExcludedWebsiteResourceModel->delete($sellerGroupId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not delete seller group with its excluded websites.')
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteByWebsite(int $websiteId): bool
    {
        try {
            return (bool)$this->groupExcludedWebsiteResourceModel->deleteByWebsite($websiteId);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('Could not delete seller group excluded website by id.')
            );
        }
    }
}
