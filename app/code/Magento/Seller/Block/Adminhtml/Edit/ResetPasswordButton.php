<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ResetPasswordButton
 */
class ResetPasswordButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        $sellerId = $this->getSellerId();
        $data = [];
        if ($sellerId) {
            $data = [
                'label' => __('Reset Password'),
                'class' => 'reset reset-password',
                'on_click' => sprintf("location.href = '%s';", $this->getResetPasswordUrl()),
                'sort_order' => 60,
                'aclResource' => 'Magento_Seller::reset_password',
            ];
        }
        return $data;
    }

    /**
     * Get reset password url
     *
     * @return string
     */
    public function getResetPasswordUrl()
    {
        return $this->getUrl('seller/index/resetPassword', ['seller_id' => $this->getSellerId()]);
    }
}
