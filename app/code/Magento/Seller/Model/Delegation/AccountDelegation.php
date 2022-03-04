<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Delegation;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\AccountDelegationInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * {@inheritdoc}
 */
class AccountDelegation implements AccountDelegationInterface
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param RedirectFactory $redirectFactory
     * @param Storage $storage
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Storage $storage
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function createRedirectForNew(
        SellerInterface $seller,
        array $mixedData = null
    ): Redirect {
        $this->storage->storeNewOperation($seller, $mixedData);

        return $this->redirectFactory->create()->setPath('seller/account/create');
    }
}
