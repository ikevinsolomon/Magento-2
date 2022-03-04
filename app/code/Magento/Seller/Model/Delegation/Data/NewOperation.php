<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Delegation\Data;

use Magento\Seller\Api\Data\SellerInterface;

/**
 * Data required for delegated new-account operation.
 */
class NewOperation
{
    /**
     * @var SellerInterface
     */
    private $seller;

    /**
     * @var array
     */
    private $additionalData;

    /**
     * @param SellerInterface $seller
     * @param array $additionalData
     */
    public function __construct(
        SellerInterface $seller,
        array $additionalData
    ) {
        $this->seller = $seller;
        $this->additionalData = $additionalData;
    }

    /**
     * @return SellerInterface
     */
    public function getSeller(): SellerInterface
    {
        return $this->seller;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}
