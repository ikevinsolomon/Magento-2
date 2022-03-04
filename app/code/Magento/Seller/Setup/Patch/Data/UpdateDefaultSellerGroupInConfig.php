<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Model\GroupManagement;
use Magento\Seller\Model\Vat;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update default seller group id in seller configuration if it's value is NULL
 */
class UpdateDefaultSellerGroupInConfig implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var GroupManagement
     */
    private $groupManagement;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupManagement $groupManagement
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupManagement $groupManagement
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupManagement = $groupManagement;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $sellerGroups = $this->groupManagement->getLoggedInGroups();
        $commonGroup = array_shift($sellerGroups);

        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['value' => $commonGroup->getId()],
            [
                'value is ?' => new \Zend_Db_Expr('NULL'),
                'path = ?' => GroupManagement::XML_PATH_DEFAULT_ID,
            ]
        );

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
}
