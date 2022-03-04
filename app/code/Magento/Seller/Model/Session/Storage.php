<?php
/**
 * Seller session storage
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Session;

class Storage extends \Magento\Framework\Session\Storage
{
    /**
     * @param \Magento\Seller\Model\Config\Share $configShare
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $namespace
     * @param array $data
     */
    public function __construct(
        \Magento\Seller\Model\Config\Share $configShare,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $namespace = 'seller',
        array $data = []
    ) {
        if ($configShare->isWebsiteScope()) {
            $namespace .= '_' . $storeManager->getWebsite()->getCode();
        }
        parent::__construct($namespace, $data);
    }
}
