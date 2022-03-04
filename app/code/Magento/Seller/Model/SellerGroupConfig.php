<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model;

/**
 * System configuration operations for seller groups.
 */
class SellerGroupConfig implements \Magento\Seller\Api\SellerGroupConfigInterface
{
    /**
     * @var \Magento\Config\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Seller\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param \Magento\Config\Model\Config $config
     * @param \Magento\Seller\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Config\Model\Config $config,
        \Magento\Seller\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->config = $config;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultSellerGroup($id)
    {
        if ($this->groupRepository->getById($id)) {
            $this->config->setDataByPath(
                \Magento\Seller\Model\GroupManagement::XML_PATH_DEFAULT_ID,
                $id
            );
            $this->config->save();
        }

        return $id;
    }
}
