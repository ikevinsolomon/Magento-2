<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Indexer\Address;

use Magento\Seller\Model\ResourceModel\Address\Attribute\Collection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute;

class AttributeProvider extends \Magento\Seller\Model\Indexer\AttributeProvider
{
    /**
     * EAV entity
     */
    const ENTITY = 'seller_address';

    /**
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        parent::__construct($eavConfig);
    }
}
