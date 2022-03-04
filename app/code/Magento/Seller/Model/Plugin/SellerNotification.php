<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\Plugin;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\Seller\NotificationStorage;
use Magento\Seller\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Refresh the Seller session if `UpdateSession` notification registered
 */
class SellerNotification
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var NotificationStorage
     */
    private $notificationStorage;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface|\Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Initialize dependencies.
     *
     * @param Session $session
     * @param NotificationStorage $notificationStorage
     * @param State $state
     * @param SellerRepositoryInterface $sellerRepository
     * @param LoggerInterface $logger
     * @param RequestInterface|null $request
     */
    public function __construct(
        Session $session,
        NotificationStorage $notificationStorage,
        State $state,
        SellerRepositoryInterface $sellerRepository,
        LoggerInterface $logger,
        RequestInterface $request
    ) {
        $this->session = $session;
        $this->notificationStorage = $notificationStorage;
        $this->state = $state;
        $this->sellerRepository = $sellerRepository;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * Refresh the seller session on frontend post requests if an update session notification is registered.
     *
     * @param ActionInterface $subject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $sellerId = $this->session->getSellerId();

        if ($this->isFrontendRequest() && $this->isPostRequest() && $this->isSessionUpdateRegisteredFor($sellerId)) {
            try {
                $this->session->regenerateId();
                $seller = $this->sellerRepository->getById($sellerId);
                $this->session->setSellerData($seller);
                $this->session->setSellerGroupId($seller->getGroupId());
                $this->notificationStorage->remove(NotificationStorage::UPDATE_SELLER_SESSION, $seller->getId());
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e);
            }
        }
    }

    /**
     * Because RequestInterface has no isPost method the check is requied before calling it.
     *
     * @return bool
     */
    private function isPostRequest(): bool
    {
        return $this->request instanceof HttpRequestInterface && $this->request->isPost();
    }

    /**
     * Check if the current application area is frontend.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFrontendRequest(): bool
    {
        return $this->state->getAreaCode() === Area::AREA_FRONTEND;
    }

    /**
     * True if the session for the given seller ID needs to be refreshed.
     *
     * @param int $sellerId
     * @return bool
     */
    private function isSessionUpdateRegisteredFor($sellerId): bool
    {
        return $this->notificationStorage->isExists(NotificationStorage::UPDATE_SELLER_SESSION, $sellerId);
    }
}
