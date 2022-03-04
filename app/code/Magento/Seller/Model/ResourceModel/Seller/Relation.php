<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\ResourceModel\Seller;

/**
 * Class Relation
 */
class Relation implements \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface
{
    /**
     * Save relations for Seller
     *
     * @param \Magento\Framework\Model\AbstractModel $seller
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $seller)
    {
        $defaultBillingId = $seller->getData('default_billing');
        $defaultShippingId = $seller->getData('default_shipping');

        if (!$seller->getData('ignore_validation_flag')) {
            /** @var \Magento\Seller\Model\Address $address */
            foreach ($seller->getAddresses() as $address) {
                if ($address->getData('_deleted')) {
                    if ($address->getId() == $defaultBillingId) {
                        $seller->setData('default_billing', null);
                    }

                    if ($address->getId() == $defaultShippingId) {
                        $seller->setData('default_shipping', null);
                    }

                    $removedAddressId = $address->getId();
                    $address->delete();

                    // Remove deleted address from seller address collection
                    $seller->getAddressesCollection()->removeItemByKey($removedAddressId);
                } else {
                    $address->setParentId(
                        $seller->getId()
                    )->setStoreId(
                        $seller->getStoreId()
                    )->setIsSellerSaveTransaction(
                        true
                    )->save();

                    if (($address->getIsPrimaryBilling() ||
                            $address->getIsDefaultBilling()) && $address->getId() != $defaultBillingId
                    ) {
                        $seller->setData('default_billing', $address->getId());
                    }

                    if (($address->getIsPrimaryShipping() ||
                            $address->getIsDefaultShipping()) && $address->getId() != $defaultShippingId
                    ) {
                        $seller->setData('default_shipping', $address->getId());
                    }
                }
            }
        }

        $changedAddresses = [];

        $changedAddresses['default_billing'] = $seller->getData('default_billing');
        $changedAddresses['default_shipping'] = $seller->getData('default_shipping');

        $seller->getResource()->getConnection()->update(
            $seller->getResource()->getTable('seller_entity'),
            $changedAddresses,
            $seller->getResource()->getConnection()->quoteInto('entity_id = ?', $seller->getId())
        );
    }
}
