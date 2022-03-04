<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Console\Command;

use Magento\Seller\Model\Seller;
use Magento\Framework\Encryption\Encryptor;
use Magento\Seller\Model\ResourceModel\Seller\Collection;
use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upgrade users passwords to the new algorithm
 */
class UpgradeHashAlgorithmCommand extends Command
{
    /**
     * @var CollectionFactory
     */
    private $sellerCollectionFactory;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param CollectionFactory $sellerCollectionFactory
     * @param Encryptor $encryptor
     */
    public function __construct(
        CollectionFactory $sellerCollectionFactory,
        Encryptor $encryptor
    ) {
        parent::__construct();
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('seller:hash:upgrade')
            ->setDescription('Upgrade seller\'s hash according to the latest algorithm');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->collection = $this->sellerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $sellerCollection = $this->collection->getItems();
        /** @var $seller Seller */
        foreach ($sellerCollection as $seller) {
            $seller->load($seller->getId());
            if (!$this->encryptor->validateHashVersion($seller->getPasswordHash())) {
                list($hash, $salt, $version) = explode(Encryptor::DELIMITER, $seller->getPasswordHash(), 3);
                $hash = $this->encryptor->getHash($hash, $salt, $this->encryptor->getLatestHashVersion());
                list($hash, $salt, $newVersion) = explode(Encryptor::DELIMITER, $hash, 3);
                $hash = implode(Encryptor::DELIMITER, [$hash, $salt, $version .Encryptor::DELIMITER .$newVersion]);
                $seller->setPasswordHash($hash);
                $seller->save();
                $output->write(".");
            }
        }
        $output->writeln(".");
        $output->writeln("<info>Finished</info>");

        return 0;
    }
}
