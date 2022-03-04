<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Setup\Patch\Data;

use Magento\Seller\Setup\SellerSetupFactory;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update passwordHash and address
 */
class UpgradePasswordHashAndAddress implements DataPatchInterface, PatchVersionInterface
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
        $this->upgradeHash();
        $entityAttributes = [
            'seller_address' => [
                'fax' => [
                    'is_visible' => false,
                    'is_system' => false,
                ],
            ],
        ];
        $sellerSetup = $this->sellerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $sellerSetup->upgradeAttributes($entityAttributes);

        return $this;
    }

    /**
     * Password hash upgrade
     *
     * @return void
     */
    private function upgradeHash()
    {
        $sellerEntityTable = $this->moduleDataSetup->getTable('seller_entity');

        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $sellerEntityTable,
            ['entity_id', 'password_hash']
        );

        $sellers = $this->moduleDataSetup->getConnection()->fetchAll($select);
        foreach ($sellers as $seller) {
            if ($seller['password_hash'] === null) {
                continue;
            }
            list($hash, $salt) = explode(Encryptor::DELIMITER, $seller['password_hash']);

            $newHash = $seller['password_hash'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password_hash' => $newHash];
            $where = ['entity_id = ?' => (int)$seller['entity_id']];
            $this->moduleDataSetup->getConnection()->update($sellerEntityTable, $bind, $where);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            AddSellerUpdatedAtAttribute::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.5';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
