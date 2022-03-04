<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 *
 * @package Magento\Seller\Block\Adminhtml\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
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
     * Get button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        $sellerId = $this->getSellerId();
        $canModify = $sellerId && !$this->sellerAccountManagement->isReadonly($this->getSellerId());
        $data = [];
        if ($sellerId && $canModify) {
            $data = [
                'label' => __('Delete Seller'),
                'class' => 'delete',
                'id' => 'seller-edit-delete-button',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl()
                ],
                'on_click' => '',
                'sort_order' => 20,
                'aclResource' => 'Magento_Seller::delete',
            ];
        }
        return $data;
    }

    /**
     * Get delete url.
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getSellerId()]);
    }
}
