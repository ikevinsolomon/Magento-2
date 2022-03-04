<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Seller;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\ResourceModel\Seller as SellerResource;
use Magento\Framework\AuthorizationInterface;
use Magento\Integration\Api\AuthorizationServiceInterface as AuthorizationService;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checks if seller is logged in and authorized in the current store
 */
class Authorization implements AuthorizationInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var SellerFactory
     */
    private $sellerFactory;

    /**
     * @var SellerResource
     */
    private $sellerResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Authorization constructor.
     *
     * @param UserContextInterface $userContext
     * @param SellerFactory $sellerFactory
     * @param SellerResource $sellerResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UserContextInterface $userContext,
        SellerFactory $sellerFactory,
        SellerResource $sellerResource,
        StoreManagerInterface $storeManager
    ) {
        $this->userContext = $userContext;
        $this->sellerFactory = $sellerFactory;
        $this->sellerResource = $sellerResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function isAllowed($resource, $privilege = null)
    {
        if ($resource === AuthorizationService::PERMISSION_SELF
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_SELLER
        ) {
            $seller = $this->sellerFactory->create();
            $this->sellerResource->load($seller, $this->userContext->getUserId());
            $currentStoreId = $this->storeManager->getStore()->getId();
            $sharedStoreIds = $seller->getSharedStoreIds();

            return in_array($currentStoreId, $sharedStoreIds);
        }

        return false;
    }
}
