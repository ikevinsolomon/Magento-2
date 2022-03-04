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
 * Class add non specified gender attribute option to seller
 */
class AddNonSpecifiedGenderAttributeOption implements DataPatchInterface, PatchVersionInterface
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
        $entityTypeId = $sellerSetup->getEntityTypeId(Seller::ENTITY);
        $attributeId = $sellerSetup->getAttributeId($entityTypeId, 'gender');

        $option = ['attribute_id' => $attributeId, 'values' => [3 => 'Not Specified']];
        $sellerSetup->addAttributeOption($option);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpdateSellerAttributesMetadata::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.2';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
