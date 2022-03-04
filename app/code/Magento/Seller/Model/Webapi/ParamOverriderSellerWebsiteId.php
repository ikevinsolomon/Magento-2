<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%seller_website_id%" value with the real seller id
 */
class ParamOverriderSellerWebsiteId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @param UserContextInterface $userContext
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(UserContextInterface $userContext, SellerRepositoryInterface $sellerRepository)
    {
        $this->userContext = $userContext;
        $this->sellerRepository = $sellerRepository;
    }

    /**
     * @inheritDoc
     */
    public function getOverriddenValue()
    {
        if ((int) $this->userContext->getUserType() === UserContextInterface::USER_TYPE_SELLER) {
            return $this->sellerRepository->getById($this->userContext->getUserId())->getWebsiteId();
        }

        return null;
    }
}
