<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Setup\SellerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update attribute input filters for seller
 */
class UpdateSellerAttributeInputFilters implements DataPatchInterface, PatchVersionInterface
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
     * @inheritdoc
     */
    public function apply()
    {
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityAttributes = [
            'seller_address' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
            'seller' => [
                'firstname' => [
                    'input_filter' => 'trim'
                ],
                'lastname' => [
                    'input_filter' => 'trim'
                ],
                'middlename' => [
                    'input_filter' => 'trim'
                ],
            ],
        ];
        $sellerSetup->upgradeAttributes($entityAttributes);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpdateVATNumber::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.13';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
