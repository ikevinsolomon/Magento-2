<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class observer UpgradeQuoteSellerEmailObserver
 */
class UpgradeQuoteSellerEmailObserver implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Upgrade quote seller email when seller has changed email
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Seller\Model\Data\Seller $sellerOrig */
        $sellerOrig = $observer->getEvent()->getOrigSellerDataObject();
        if (!$sellerOrig) {
            return;
        }

        $emailOrig = $sellerOrig->getEmail();

        /** @var \Magento\Seller\Model\Data\Seller $seller */
        $seller = $observer->getEvent()->getSellerDataObject();
        $email = $seller->getEmail();

        if ($email == $emailOrig) {
            return;
        }

        try {
            $quote = $this->quoteRepository->getForSeller($seller->getId());
            $quote->setSellerEmail($email);
            $this->quoteRepository->save($quote);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }
}
