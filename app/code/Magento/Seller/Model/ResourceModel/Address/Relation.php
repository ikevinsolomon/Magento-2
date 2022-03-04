<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\ResourceModel\Address;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Model\Address;
use Magento\Seller\Model\Seller;
use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\SellerRegistry;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class represents save operations for seller address relations
 */
class Relation implements RelationInterface
{
    /**
     * @var SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var SellerRegistry
     */
    private $sellerRegistry;

    /**
     * @param SellerFactory $sellerFactory
     * @param SellerRegistry $sellerRegistry
     */
    public function __construct(
        SellerFactory $sellerFactory,
        SellerRegistry $sellerRegistry
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->sellerRegistry = $sellerRegistry;
    }

    /**
     * Process object relations
     *
     * @param AbstractModel $object
     * @return void
     */
    public function processRelation(AbstractModel $object): void
    {
        /** @var $object Address */
        if (!$object->getIsSellerSaveTransaction() && $object->getId()) {
            $seller = $this->sellerFactory->create()->load($object->getSellerId());

            $changedAddresses = [];
            $changedAddresses = $this->getDefaultBillingChangedAddress($object, $seller, $changedAddresses);
            $changedAddresses = $this->getDefaultShippingChangedAddress($object, $seller, $changedAddresses);

            if ($changedAddresses) {
                $seller->getResource()->getConnection()->update(
                    $seller->getResource()->getTable('seller_entity'),
                    $changedAddresses,
                    $seller->getResource()->getConnection()->quoteInto('entity_id = ?', $seller->getId())
                );
                $this->updateSellerRegistry($seller, $changedAddresses);
            }
        }
    }

    /**
     * Get default billing changed address
     *
     * @param Address $object
     * @param Seller $seller
     * @param array $changedAddresses
     * @return array
     */
    private function getDefaultBillingChangedAddress(
        Address $object,
        Seller $seller,
        array $changedAddresses
    ): array {
        if ($object->getIsDefaultBilling()) {
            $changedAddresses[SellerInterface::DEFAULT_BILLING] = $object->getId();
        } elseif ($seller->getDefaultBillingAddress()
            && $object->getIsDefaultBilling() === false
            && (int)$seller->getDefaultBillingAddress()->getId() === (int)$object->getId()
        ) {
            $changedAddresses[SellerInterface::DEFAULT_BILLING] = null;
        }

        return $changedAddresses;
    }

    /**
     * Get default shipping changed address
     *
     * @param Address $object
     * @param Seller $seller
     * @param array $changedAddresses
     * @return array
     */
    private function getDefaultShippingChangedAddress(
        Address $object,
        Seller $seller,
        array $changedAddresses
    ): array {
        if ($object->getIsDefaultShipping()) {
            $changedAddresses[SellerInterface::DEFAULT_SHIPPING] = $object->getId();
        } elseif ($seller->getDefaultShippingAddress()
            && $object->getIsDefaultShipping() === false
            && (int)$seller->getDefaultShippingAddress()->getId() === (int)$object->getId()
        ) {
            $changedAddresses[SellerInterface::DEFAULT_SHIPPING] = null;
        }

        return $changedAddresses;
    }

    /**
     * Push updated seller entity to the registry.
     *
     * @param Seller $seller
     * @param array $changedAddresses
     * @return void
     */
    private function updateSellerRegistry(Seller $seller, array $changedAddresses): void
    {
        if (array_key_exists(SellerInterface::DEFAULT_BILLING, $changedAddresses)) {
            $seller->setDefaultBilling($changedAddresses[SellerInterface::DEFAULT_BILLING]);
        }

        if (array_key_exists(SellerInterface::DEFAULT_SHIPPING, $changedAddresses)) {
            $seller->setDefaultShipping($changedAddresses[SellerInterface::DEFAULT_SHIPPING]);
        }

        $this->sellerRegistry->push($seller);
    }

    /**
     * Checks if address has chosen as default and has had an id
     *
     * @deprecated 102.0.1 Is not used anymore due to changes in logic of save of address.
     *             If address was default and becomes not default than default address id for seller must be
     *             set to null
     * @param AbstractModel $object
     * @return bool
     */
    protected function isAddressDefault(AbstractModel $object)
    {
        return $object->getId() && ($object->getIsDefaultBilling() || $object->getIsDefaultShipping());
    }
}
