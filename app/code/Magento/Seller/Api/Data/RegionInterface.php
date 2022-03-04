<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Seller address region interface.
 * @api
 * @since 100.0.2
 */
interface RegionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the getters in snake case
     */
    const REGION_CODE = 'region_code';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    /**#@-*/

    /**
     * Get region code
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Set region code
     *
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode);

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Get region id
     *
     * @return int
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Seller\Api\Data\RegionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Seller\Api\Data\RegionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Seller\Api\Data\RegionExtensionInterface $extensionAttributes);
}
