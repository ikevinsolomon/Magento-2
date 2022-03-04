<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Seller\Api\SellerManagementInterface;
use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory;

class SellerManagement implements SellerManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $sellersFactory;

    /**
     * @param CollectionFactory $sellersFactory
     */
    public function __construct(CollectionFactory $sellersFactory)
    {
        $this->sellersFactory = $sellersFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $sellers = $this->sellersFactory->create();
        /** @var \Magento\Seller\Model\ResourceModel\Seller\Collection $sellers */
        return $sellers->getSize();
    }
}
