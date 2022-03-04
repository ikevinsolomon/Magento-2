<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @package Magento\Seller\Block\Adminhtml\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AccountManagementInterface $sellerAccountManagement
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        AccountManagementInterface $sellerAccountManagement
    ) {
        parent::__construct($context, $registry);
        $this->sellerAccountManagement = $sellerAccountManagement;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $sellerId = $this->getSellerId();
        $canModify = !$sellerId || !$this->sellerAccountManagement->isReadonly($this->getSellerId());
        $data = [];
        if ($canModify) {
            $data = [
                'label' => __('Save Seller'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 90,
            ];
        }
        return $data;
    }
}
