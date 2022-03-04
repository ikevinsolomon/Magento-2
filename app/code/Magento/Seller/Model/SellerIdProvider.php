<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model;

use Magento\Framework\App\RequestInterface;

/**
 * Provides seller id from request.
 */
class SellerIdProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get seller id from request.
     *
     * @return int
     */
    public function getSellerId(): int
    {
        return (int)$this->request->getParam('id');
    }
}
