<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\StoreSwitcher;

use Magento\Seller\Model\SellerRegistry;
use Magento\Seller\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPostprocessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Process seller data redirected from origin store
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RedirectDataPostprocessor implements RedirectDataPostprocessorInterface
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var SellerRegistry
     */
    private $sellerRegistry;

    /**
     * @param SellerRegistry $sellerRegistry
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        SellerRegistry $sellerRegistry,
        Session $session,
        LoggerInterface $logger
    ) {
        $this->sellerRegistry = $sellerRegistry;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(ContextInterface $context, array $data): void
    {
        if (!empty($data['seller_id'])) {
            try {
                $seller = $this->sellerRegistry->retrieve($data['seller_id']);
                if (!$this->session->isLoggedIn()
                    && in_array($context->getTargetStore()->getId(), $seller->getSharedStoreIds())
                ) {
                    $this->session->setSellerDataAsLoggedIn($seller->getDataModel());
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e);
                throw new LocalizedException(__('Something went wrong.'), $e);
            } catch (LocalizedException $e) {
                $this->logger->error($e);
                throw new LocalizedException(__('Something went wrong.'), $e);
            }
        }
    }
}
