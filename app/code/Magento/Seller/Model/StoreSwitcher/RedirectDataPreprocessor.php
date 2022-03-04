<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\StoreSwitcher;

use Magento\Seller\Model\SellerRegistry;
use Magento\Seller\Model\Session;
use Magento\Store\Model\StoreSwitcher\ContextInterface;
use Magento\Store\Model\StoreSwitcher\RedirectDataPreprocessorInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Collect seller data to be redirected to target store
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class RedirectDataPreprocessor implements RedirectDataPreprocessorInterface
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
    public function process(ContextInterface $context, array $data): array
    {
        if ($this->session->isLoggedIn()) {
            try {
                $seller = $this->sellerRegistry->retrieve($this->session->getSellerId());
                if (in_array($context->getTargetStore()->getId(), $seller->getSharedStoreIds())) {
                    $data['seller_id'] = (int) $seller->getId();
                }
            } catch (Throwable $e) {
                $this->logger->error($e);
            }
        }

        return $data;
    }
}
