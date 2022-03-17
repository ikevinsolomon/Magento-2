<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Smtp
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Honasa\Review\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\Smtp\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            if ($connection->tableColumnExists('review_detail', 'images') === false) {
                $connection->addColumn(
                    $setup->getTable('review_detail'),
                    'images',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 1255,
                        'nullable' => true,
                        'comment' => 'review images',
                    ]
                );
            }
            if ($connection->tableColumnExists('review_detail', 'videos') === false) {
                $connection->addColumn(
                    $setup->getTable('review_detail'),
                    'videos',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 1255,
                        'nullable' => true,
                        'comment' => 'review videos',
                    ]
                );
            }
        }
        $setup->endSetup();
    }
}
