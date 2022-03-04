<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class InvalidateTokenButton
 *
 * @package Magento\Seller\Block\Adminhtml\Edit
 */
class InvalidateTokenButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        $sellerId = $this->getSellerId();
        $data = [];
        if ($sellerId) {
            $deleteConfirmMsg = __("Are you sure you want to revoke the seller's tokens?");
            $data = [
                'label' => __('Force Sign-In'),
                'class' => 'invalidate-token',
                'on_click' => 'deleteConfirm("' . $deleteConfirmMsg . '", "' . $this->getInvalidateTokenUrl() . '")',
                'sort_order' => 65,
                'aclResource' => 'Magento_Seller::invalidate_tokens',
            ];
        }
        return $data;
    }

    /**
     * Get invalidate token url.
     *
     * @return string
     */
    public function getInvalidateTokenUrl()
    {
        return $this->getUrl('seller/seller/invalidateToken', ['seller_id' => $this->getSellerId()]);
    }
}
