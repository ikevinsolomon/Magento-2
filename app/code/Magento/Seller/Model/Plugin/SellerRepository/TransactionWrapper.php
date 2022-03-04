<?php
/**
 * Plugin for \Magento\Seller\Api\SellerRepositoryInterface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Plugin\SellerRepository;

class TransactionWrapper
{
    /**
     * @var \Magento\Seller\Model\ResourceModel\Seller
     */
    protected $resourceModel;

    /**
     * @param \Magento\Seller\Model\ResourceModel\Seller $resourceModel
     */
    public function __construct(
        \Magento\Seller\Model\ResourceModel\Seller $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param \Magento\Seller\Api\SellerRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @param string $passwordHash
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Seller\Api\SellerRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Seller\Api\Data\SellerInterface $seller,
        $passwordHash = null
    ) {
        $this->resourceModel->beginTransaction();
        try {
            /** @var $result \Magento\Seller\Api\Data\SellerInterface */
            $result = $proceed($seller, $passwordHash);
            $this->resourceModel->commit();
            return $result;
        } catch (\Exception $e) {
            $this->resourceModel->rollBack();
            throw $e;
        }
    }
}
