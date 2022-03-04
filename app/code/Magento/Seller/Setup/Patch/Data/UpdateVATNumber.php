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
 * Upgrade vat number
 */
class UpdateVATNumber implements DataPatchInterface, PatchVersionInterface
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
        $sellerSetup = $this->sellerSetupFactory->create(['resourceConnection' => $this->moduleDataSetup]);
        $sellerSetup->updateAttribute(
            'seller_address',
            'vat_id',
            'frontend_label',
            'VAT Number'
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            ConvertValidationRulesFromSerializedToJson::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.12';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
