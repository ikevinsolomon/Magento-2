<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Seller\Model\SellerRegistry;

/**
 * Class UnlockButton
 */
class UnlockButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Seller\Model\SellerRegistry
     */
    protected $sellerRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Seller\Model\SellerRegistry $sellerRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        SellerRegistry $sellerRegistry
    ) {
        parent::__construct($context, $registry);
        $this->sellerRegistry = $sellerRegistry;
    }

    /**
     * Returns Unlock button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $sellerId = $this->getSellerId();
        $data = [];
        if ($sellerId) {
            $seller = $this->sellerRegistry->retrieve($sellerId);
            if ($seller->isSellerLocked()) {
                $data = [
                    'label' => __('Unlock'),
                    'class' => 'unlock unlock-seller',
                    'on_click' => sprintf("location.href = '%s';", $this->getUnlockUrl()),
                    'sort_order' => 50,
                ];
            }
        }
        return $data;
    }

    /**
     * Returns seller unlock action URL
     *
     * @return string
     */
    protected function getUnlockUrl()
    {
        return $this->getUrl('seller/locks/unlock', ['seller_id' => $this->getSellerId()]);
    }
}
