<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Setup\SellerSetup;
use Magento\Seller\Setup\SellerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend;
use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Class default groups and attributes for seller
 */
class DefaultSellerGroupsAndAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var SellerSetupFactory
     */
    private $sellerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param SellerSetupFactory $sellerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        SellerSetupFactory $sellerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->sellerSetupFactory = $sellerSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        /** @var SellerSetup $sellerSetup */
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // insert default seller groups
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('seller_group'),
            ['seller_group_id' => 0, 'seller_group_code' => 'NOT LOGGED IN', 'tax_class_id' => 3]
        );
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('seller_group'),
            ['seller_group_id' => 1, 'seller_group_code' => 'General', 'tax_class_id' => 3]
        );
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('seller_group'),
            ['seller_group_id' => 2, 'seller_group_code' => 'Wholesale', 'tax_class_id' => 3]
        );
        $this->moduleDataSetup->getConnection()->insertForce(
            $this->moduleDataSetup->getTable('seller_group'),
            ['seller_group_id' => 3, 'seller_group_code' => 'Retailer', 'tax_class_id' => 3]
        );

        $sellerSetup->installEntities();

        $sellerSetup->installSellerForms();

        $disableAGCAttribute = $sellerSetup->getEavConfig()->getAttribute('seller', 'disable_auto_group_change');
        $disableAGCAttribute->setData('used_in_forms', ['adminhtml_seller']);
        $disableAGCAttribute->save();

        $attributesInfo = [
            'vat_id' => [
                'label' => 'VAT number',
                'type' => 'static',
                'input' => 'text',
                'position' => 140,
                'visible' => true,
                'required' => false,
            ],
            'vat_is_valid' => [
                'label' => 'VAT number validity',
                'visible' => false,
                'required' => false,
                'type' => 'static',
            ],
            'vat_request_id' => [
                'label' => 'VAT number validation request ID',
                'type' => 'static',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_date' => [
                'label' => 'VAT number validation request date',
                'type' => 'static',
                'visible' => false,
                'required' => false,
            ],
            'vat_request_success' => [
                'label' => 'VAT number validation request success',
                'visible' => false,
                'required' => false,
                'type' => 'static',
            ],
        ];

        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $sellerSetup->addAttribute('seller_address', $attributeCode, $attributeParams);
        }

        $vatIdAttribute = $sellerSetup->getEavConfig()->getAttribute('seller_address', 'vat_id');
        $vatIdAttribute->setData(
            'used_in_forms',
            ['adminhtml_seller_address', 'seller_address_edit', 'seller_register_address']
        );
        $vatIdAttribute->save();

        $entities = $sellerSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $sellerSetup->addEntityType($entityName, $entity);
        }

        $sellerSetup->updateAttribute(
            'seller_address',
            'street',
            'backend_model',
            DefaultBackend::class
        );

        $migrationSetup = $this->moduleDataSetup->createMigrationSetup();

        $migrationSetup->appendClassAliasReplace(
            'seller_eav_attribute',
            'data_model',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['attribute_id']
        );
        $migrationSetup->doUpdateClassAliases();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
