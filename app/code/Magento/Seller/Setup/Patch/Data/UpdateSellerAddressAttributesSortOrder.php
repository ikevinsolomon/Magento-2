<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Setup\SellerSetup;
use Magento\Seller\Setup\SellerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update Seller Address Attributes to be displayed in following order: country, region, city, postcode
 */
class UpdateSellerAddressAttributesSortOrder implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SellerSetupFactory
     */
    private $sellerSetupFactory;

    /**
     * UpdateSellerAddressAttributesSortOrder constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SellerSetupFactory $sellerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SellerSetupFactory $sellerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->sellerSetupFactory = $sellerSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        /** @var SellerSetup $sellerSetup */
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->updateSellerAddressAttributesSortOrder($sellerSetup);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            DefaultSellerGroupsAndAttributes::class,
        ];
    }

    /**
     * Update seller address attributes sort order
     *
     * @param SellerSetup $sellerSetup
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function updateSellerAddressAttributesSortOrder($sellerSetup)
    {
        $entityAttributes = [
            'seller_address' => [
                'country_id' => [
                    'sort_order' => 80,
                    'position' => 80
                ],
                'region' => [
                    'sort_order' => 90,
                    'position' => 90
                ],
                'region_id' => [
                    'sort_order' => 90,
                    'position' => 90
                ],
                'city' => [
                    'sort_order' => 100,
                    'position' => 100
                ],
            ],
        ];

        $sellerSetup->upgradeAttributes($entityAttributes);
    }
}
