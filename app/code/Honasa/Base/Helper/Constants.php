<?php

namespace Honasa\Base\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Constants extends AbstractHelper
{

    public function getAttributesToRemove()
    { 
        $attributes = $this->scopeConfig->getValue('honasa_config/attributes/attributes_to_skip', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return isset($attributes) ? explode(',', $attributes) : [];
    }

    public function getSQSKey(){
        return $this->scopeConfig->getValue('honasa_config/sqs/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSQSSecret(){
        return $this->scopeConfig->getValue('honasa_config/sqs/secret', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSQSRegion(){
        return $this->scopeConfig->getValue('honasa_config/sqs/region', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getSQSCatalogQueue(){
        return $this->scopeConfig->getValue('honasa_config/sqs/catalog_queue', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getSQSCatalogMessageGroupId(){
        return $this->scopeConfig->getValue('honasa_config/sqs/catalog_message_group_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}