<?php
namespace Magento\GiftCardAccount\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
	
	private $customerSetupFactory;
	 
	/**
	 * Constructor
	 *
	 * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
	 */
	public function __construct(
		CustomerSetupFactory $customerSetupFactory
	) {
		$this->customerSetupFactory = $customerSetupFactory;
	}

	/**
	 * Upgrades DB schema for a module
	 *
	 * @param ModuleDataSetupInterface $setup
	 * @param ModuleContextInterface $context
	 * @return void
	 */
	public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		
		// for magento_giftcardaccount table
		$magentoGiftcardaccountTable = $installer->getTable('magento_giftcardaccount');

		$columns = [
			'recipient_email' => [
				'type' => Table::TYPE_TEXT,
				'length' => 255,
				'comment' => 'Recipient Email'
			], 
			'recipient_name' => [
				'type' => Table::TYPE_TEXT,
				'length' => 255,
				'comment' => 'Recipient Name'
			],
		];
		
		$connection = $installer->getConnection();
		foreach ($columns as $name => $definition) {
			$connection->addColumn($magentoGiftcardaccountTable, $name, $definition);
		}
		
		$installer->endSetup();
		
	}
}