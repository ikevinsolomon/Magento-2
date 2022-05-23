<?php

namespace Honasa\Base\Model;

use \Magento\Framework\App\Helper\AbstractHelper;
use Predis\Client as Client;

class Redis extends AbstractHelper
{
    public function getRedisClient()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $redisHost = $scopeConfig->getValue('node_redis_server/config/host', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $redisPort = $scopeConfig->getValue('node_redis_server/config/port', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $client = new Client([
            'scheme' => 'tcp',
            'host' => $redisHost,
            'port' => $redisPort
        ]);
        return $client;
    }
}