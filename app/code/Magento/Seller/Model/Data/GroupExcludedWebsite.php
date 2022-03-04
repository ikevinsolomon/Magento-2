<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Data;

use Magento\Seller\Api\Data\GroupExcludedWebsiteExtensionInterface;
use Magento\Seller\Api\Data\GroupExcludedWebsiteInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Seller Group Excluded Website data model.
 */
class GroupExcludedWebsite extends AbstractExtensibleModel implements GroupExcludedWebsiteInterface
{
    /**
     * Define resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Seller\Model\ResourceModel\GroupExcludedWebsite::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupWebsiteId(): ?int
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupWebsiteId(int $id): GroupExcludedWebsiteInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupId(): ?int
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId(int $id): GroupExcludedWebsiteInterface
    {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getExcludedWebsiteId(): ?int
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setExcludedWebsiteId(int $websiteId): GroupExcludedWebsiteInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes(): ?GroupExcludedWebsiteExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        GroupExcludedWebsiteExtensionInterface $extensionAttributes
    ): GroupExcludedWebsiteInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
