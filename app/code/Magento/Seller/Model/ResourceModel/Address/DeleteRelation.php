<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel\Address;

use Magento\Seller\Api\Data\SellerInterface;

/**
 * Class DeleteRelation
 * @package Magento\Seller\Model\ResourceModel\Address
 */
class DeleteRelation
{
    /**
     * Delete relation (billing and shipping) between seller and address
     *
     * @param \Magento\Framework\Model\AbstractModel $address
     * @param \Magento\Seller\Model\Seller $seller
     * @return void
     */
    public function deleteRelation(
        \Magento\Framework\Model\AbstractModel $address,
        \Magento\Seller\Model\Seller $seller
    ) {
        $toUpdate = $this->getDataToUpdate($address, $seller);

        if (!$address->getIsSellerSaveTransaction() && !empty($toUpdate)) {
            $address->getResource()->getConnection()->update(
                $address->getResource()->getTable('seller_entity'),
                $toUpdate,
                $address->getResource()->getConnection()->quoteInto('entity_id = ?', $seller->getId())
            );
        }
    }

    /**
     * Return address type (billing or shipping), or null if address is not default
     *
     * @param \Magento\Seller\Api\Data\AddressInterface $address
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @return array
     */
    private function getDataToUpdate(
        \Magento\Framework\Model\AbstractModel $address,
        \Magento\Seller\Model\Seller $seller
    ) {
        $toUpdate = [];
        if ($address->getId()) {
            if ($seller->getDefaultBilling() == $address->getId()) {
                $toUpdate[SellerInterface::DEFAULT_BILLING] = null;
            }

            if ($seller->getDefaultShipping() == $address->getId()) {
                $toUpdate[SellerInterface::DEFAULT_SHIPPING] = null;
            }
        }

        return $toUpdate;
    }
}
