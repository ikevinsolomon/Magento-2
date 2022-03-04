<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Data;

/**
 * Data Model implementing Address Region interface
 *
 */
class Region extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Seller\Api\Data\RegionInterface
{
    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->_get(self::REGION_CODE);
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->_get(self::REGION);
    }

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->_get(self::REGION_ID);
    }

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::REGION_CODE, $regionCode);
    }

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Seller\Api\Data\RegionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Seller\Api\Data\RegionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Seller\Api\Data\RegionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
