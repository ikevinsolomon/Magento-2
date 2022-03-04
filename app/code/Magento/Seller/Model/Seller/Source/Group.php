<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Seller\Source;

use Magento\Seller\Api\Data\GroupSearchResultsInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Seller\Api\Data\GroupInterface;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Group.
 */
class Group implements GroupSourceInterface
{
    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param ModuleManager $moduleManager
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ModuleManager $moduleManager,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->moduleManager = $moduleManager;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Return array of seller groups
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Seller')) {
            return [];
        }
        $sellerGroups = [];
        $sellerGroups[] = [
            'label' => __('ALL GROUPS'),
            'value' => (string)GroupInterface::CUST_GROUP_ALL,
        ];

        /** @var GroupSearchResultsInterface $groups */
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($groups->getItems() as $group) {
            $sellerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        return $sellerGroups;
    }
}
