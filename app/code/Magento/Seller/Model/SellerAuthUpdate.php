<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model;

use Magento\Seller\Model\ResourceModel\Seller as SellerResourceModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Seller Authentication update model.
 */
class SellerAuthUpdate
{
    /**
     * @var SellerRegistry
     */
    protected $sellerRegistry;

    /**
     * @var SellerResourceModel
     */
    protected $sellerResourceModel;

    /**
     * @var Seller
     */
    private $sellerModel;

    /**
     * @param SellerRegistry $sellerRegistry
     * @param SellerResourceModel $sellerResourceModel
     * @param Seller|null $sellerModel
     */
    public function __construct(
        SellerRegistry $sellerRegistry,
        SellerResourceModel $sellerResourceModel,
        Seller $sellerModel = null
    ) {
        $this->sellerRegistry = $sellerRegistry;
        $this->sellerResourceModel = $sellerResourceModel;
        $this->sellerModel = $sellerModel ?: ObjectManager::getInstance()->get(Seller::class);
    }

    /**
     * Reset Authentication data for seller.
     *
     * @param int $sellerId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function saveAuth($sellerId)
    {
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($sellerId);

        $this->sellerResourceModel->load($this->sellerModel, $sellerId);
        $currentLockExpiresVal = $this->sellerModel->getData('lock_expires');
        $newLockExpiresVal = $sellerSecure->getData('lock_expires');

        $this->sellerResourceModel->getConnection()->update(
            $this->sellerResourceModel->getTable('seller_entity'),
            [
                'failures_num' => $sellerSecure->getData('failures_num'),
                'first_failure' => $sellerSecure->getData('first_failure'),
                'lock_expires' => $newLockExpiresVal,
            ],
            $this->sellerResourceModel->getConnection()->quoteInto('entity_id = ?', $sellerId)
        );

        if ($currentLockExpiresVal !== $newLockExpiresVal) {
            $this->sellerModel->reindex();
        }

        return $this;
    }
}
