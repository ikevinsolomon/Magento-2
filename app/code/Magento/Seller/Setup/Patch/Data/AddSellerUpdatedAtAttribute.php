<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Model\Seller;
use Magento\Seller\Setup\SellerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class add seller updated attribute to seller
 */
class AddSellerUpdatedAtAttribute implements DataPatchInterface, PatchVersionInterface
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
        $sellerSetup->addAttribute(
            Seller::ENTITY,
            'updated_at',
            [
                'type' => 'static',
                'label' => 'Updated At',
                'input' => 'date',
                'required' => false,
                'sort_order' => 87,
                'visible' => false,
                'system' => false,
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpdateIdentifierSellerAttributesVisibility::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.4';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
