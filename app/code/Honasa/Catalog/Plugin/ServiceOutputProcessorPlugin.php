<?php

namespace Honasa\Catalog\Plugin;

use Magento\Framework\Webapi\ServiceOutputProcessor;

class ServiceOutputProcessorPlugin
{
    public function aroundConvertValue(ServiceOutputProcessor $subject, callable $proceed, $data, $type)
    {
        if ($type == 'array') {
            return $data;
        }
        return $proceed($data, $type);
    }
}
