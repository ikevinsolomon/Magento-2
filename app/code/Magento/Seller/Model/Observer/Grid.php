<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Observer;

use Magento\Seller\Model\ResourceModel\Seller\Grid as SellerGrid;

/**
 * @deprecated 100.1.0
 */
class Grid
{
    /**
     * @var SellerGrid
     */
    protected $sellerGrid;

    /**
     * @param SellerGrid $grid
     */
    public function __construct(
        SellerGrid $grid
    ) {
        $this->sellerGrid = $grid;
    }

    /**
     * @return void
     *
     * @deprecated 100.1.0
     */
    public function syncSellerGrid()
    {
        $this->sellerGrid->syncSellerGrid();
    }
}
