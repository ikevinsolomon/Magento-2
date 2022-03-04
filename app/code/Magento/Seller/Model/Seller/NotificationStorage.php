<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Seller;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;

class NotificationStorage
{
    const UPDATE_SELLER_SESSION = 'update_seller_session';

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * NotificationStorage constructor.
     * @param FrontendInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        FrontendInterface $cache,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Add notification in cache
     *
     * @param string $notificationType
     * @param string $sellerId
     * @return void
     */
    public function add($notificationType, $sellerId)
    {
        $this->cache->save(
            $this->serializer->serialize([
                'seller_id' => $sellerId,
                'notification_type' => $notificationType
            ]),
            $this->getCacheKey($notificationType, $sellerId)
        );
    }

    /**
     * Check whether notification is exists in cache
     *
     * @param string $notificationType
     * @param string $sellerId
     * @return bool
     */
    public function isExists($notificationType, $sellerId)
    {
        return $this->cache->test($this->getCacheKey($notificationType, $sellerId));
    }

    /**
     * Remove notification from cache
     *
     * @param string $notificationType
     * @param string $sellerId
     * @return void
     */
    public function remove($notificationType, $sellerId)
    {
        $this->cache->remove($this->getCacheKey($notificationType, $sellerId));
    }

    /**
     * Retrieve cache key
     *
     * @param string $notificationType
     * @param string $sellerId
     * @return string
     */
    private function getCacheKey($notificationType, $sellerId)
    {
        return 'notification_' . $notificationType . '_' . $sellerId;
    }
}
