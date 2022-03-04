<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit\Tab;

/**
 * Obtain all carts contents for specified client
 *
 * @api
 * @since 100.0.2
 */
class Carts extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Seller\Model\Config\Share
     */
    protected $_shareConfig;

    /**
     * @var \Magento\Seller\Api\Data\SellerInterfaceFactory
     */
    protected $sellerDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context          $context
     * @param \Magento\Seller\Model\Config\Share             $shareConfig
     * @param \Magento\Seller\Api\Data\SellerInterfaceFactory $sellerDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Seller\Model\Config\Share $shareConfig,
        \Magento\Seller\Api\Data\SellerInterfaceFactory $sellerDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->_shareConfig = $shareConfig;
        $this->sellerDataFactory = $sellerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $data);
    }

    /**
     * Add shopping cart grid of each website
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $sharedWebsiteIds = $this->_shareConfig->getSharedWebsiteIds($this->_getSeller()->getWebsiteId());
        $isShared = count($sharedWebsiteIds) > 1;
        foreach ($sharedWebsiteIds as $websiteId) {
            $blockName = 'seller_cart_' . $websiteId;
            $block = $this->getLayout()->createBlock(
                \Magento\Seller\Block\Adminhtml\Edit\Tab\Cart::class,
                $blockName,
                ['data' => ['website_id' => $websiteId]]
            );
            if ($isShared) {
                $websiteName = $this->_storeManager->getWebsite($websiteId)->getName();
                $block->setCartHeader(__('Shopping Cart from %1', $websiteName));
            }
            $this->setChild($blockName, $block);
        }
        return parent::_prepareLayout();
    }

    /**
     * Just get child blocks html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('adminhtml_block_html_before', ['block' => $this]);
        return $this->getChildHtml();
    }

    /**
     * @return \Magento\Seller\Api\Data\SellerInterface
     */
    protected function _getSeller()
    {
        $sellerDataObject = $this->sellerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $sellerDataObject,
            $this->_backendSession->getSellerData()['account'],
            \Magento\Seller\Api\Data\SellerInterface::class
        );
        return $sellerDataObject;
    }
}
