<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Model\Seller;
use Magento\Seller\Model\ResourceModel\Address;
use Magento\Seller\Model\ResourceModel\Address\Attribute\Backend\Region;
use Magento\Seller\Model\ResourceModel\Address\Attribute\Source\Country;
use Magento\Seller\Model\ResourceModel\Attribute\Collection;
use Magento\Seller\Setup\SellerSetupFactory;
use Magento\Eav\Model\Entity\Increment\NumericValue;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Remove register and update attributes for checkout
 */
class RemoveCheckoutRegisterAndUpdateAttributes implements DataPatchInterface, PatchVersionInterface
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
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('seller_form_attribute'),
            ['form_code = ?' => 'checkout_register']
        );
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $sellerSetup->updateEntityType(
            Seller::ENTITY,
            'entity_model',
            \Magento\Seller\Model\ResourceModel\Seller::class
        );
        $sellerSetup->updateEntityType(
            Seller::ENTITY,
            'increment_model',
            NumericValue::class
        );
        $sellerSetup->updateEntityType(
            Seller::ENTITY,
            'entity_attribute_collection',
            Collection::class
        );
        $sellerSetup->updateEntityType(
            'seller_address',
            'entity_model',
            Address::class
        );
        $sellerSetup->updateEntityType(
            'seller_address',
            'entity_attribute_collection',
            Address\Attribute\Collection::class
        );
        $sellerSetup->updateAttribute(
            'seller_address',
            'country_id',
            'source_model',
            Country::class
        );
        $sellerSetup->updateAttribute(
            'seller_address',
            'region',
            'backend_model',
            Region::class
        );
        $sellerSetup->updateAttribute(
            'seller_address',
            'region_id',
            'source_model',
            Address\Attribute\Source\Region::class
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            UpgradePasswordHashAndAddress::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.6';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
