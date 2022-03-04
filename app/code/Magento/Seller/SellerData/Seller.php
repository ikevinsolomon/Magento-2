<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\SellerData;

use Magento\Seller\Helper\Session\CurrentSeller;
use Magento\Seller\Helper\View;

/**
 * Seller section
 */
class Seller implements SectionSourceInterface
{
    /**
     * @var CurrentSeller
     */
    protected $currentSeller;

    /**
     * @var View
     */
    private $sellerViewHelper;

    /**
     * @param CurrentSeller $currentSeller
     * @param View $sellerViewHelper
     */
    public function __construct(
        CurrentSeller $currentSeller,
        View $sellerViewHelper
    ) {
        $this->currentSeller = $currentSeller;
        $this->sellerViewHelper = $sellerViewHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if (!$this->currentSeller->getSellerId()) {
            return [];
        }

        $seller = $this->currentSeller->getSeller();
        return [
            'fullname' => $this->sellerViewHelper->getSellerName($seller),
            'firstname' => $seller->getFirstname(),
            'websiteId' => $seller->getWebsiteId(),
        ];
    }
}
