<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Controller\RegistryConstants;

/**
 * @deprecated 100.2.0 for UiComponent replacement
 * @see app/code/Magento/Seller/view/base/ui_component/seller_form.xml
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * Seller view helper
     *
     * @var \Magento\Seller\Helper\View
     */
    protected $_viewHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AccountManagementInterface $sellerAccountManagement
     * @param SellerRepositoryInterface $sellerRepository
     * @param \Magento\Seller\Helper\View $viewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        AccountManagementInterface $sellerAccountManagement,
        SellerRepositoryInterface $sellerRepository,
        \Magento\Seller\Helper\View $viewHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->sellerAccountManagement = $sellerAccountManagement;
        $this->sellerRepository = $sellerRepository;
        $this->_viewHelper = $viewHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magento_Seller';

        $sellerId = $this->getSellerId();

        if ($sellerId && $this->_authorization->isAllowed('Magento_Sales::create')) {
            $this->buttonList->add(
                'order',
                [
                    'label' => __('Create Order'),
                    'onclick' => 'setLocation(\'' . $this->getCreateOrderUrl() . '\')',
                    'class' => 'add'
                ],
                0
            );
        }

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Seller'));
        $this->buttonList->update('delete', 'label', __('Delete Seller'));

        if ($sellerId && $this->sellerAccountManagement->isReadonly($sellerId)) {
            $this->buttonList->remove('save');
            $this->buttonList->remove('reset');
        }

        if (!$sellerId || $this->sellerAccountManagement->isReadonly($sellerId)) {
            $this->buttonList->remove('delete');
        }

        if ($sellerId) {
            $url = $this->getUrl('seller/index/resetPassword', ['seller_id' => $sellerId]);
            $this->buttonList->add(
                'reset_password',
                [
                    'label' => __('Reset Password'),
                    'onclick' => 'setLocation(\'' . $url . '\')',
                    'class' => 'reset reset-password'
                ],
                0
            );
        }

        if ($sellerId) {
            $url = $this->getUrl('seller/seller/invalidateToken', ['seller_id' => $sellerId]);
            $deleteConfirmMsg = __("Are you sure you want to revoke the seller's tokens?");
            $this->buttonList->add(
                'invalidate_token',
                [
                    'label' => __('Force Sign-In'),
                    'onclick' => 'deleteConfirm(\'' . $this->escapeJs($this->escapeHtml($deleteConfirmMsg)) .
                        '\', \'' . $url . '\')',
                    'class' => 'invalidate-token'
                ],
                10
            );
        }
    }

    /**
     * Retrieve the Url for creating an order.
     *
     * @return string
     */
    public function getCreateOrderUrl()
    {
        return $this->getUrl('sales/order_create/start', ['seller_id' => $this->getSellerId()]);
    }

    /**
     * Return the seller Id.
     *
     * @return int|null
     */
    public function getSellerId()
    {
        $sellerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SELLER_ID);
        return $sellerId;
    }

    /**
     * Retrieve the header text, either the name of an existing seller or 'New Seller'.
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        $sellerId = $this->getSellerId();
        if ($sellerId) {
            $sellerData = $this->sellerRepository->getById($sellerId);
            return $this->escapeHtml($this->_viewHelper->getSellerName($sellerData));
        } else {
            return __('New Seller');
        }
    }

    /**
     * Prepare form Html. Add block for configurable product modification interface.
     *
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Composite\Configure::class
        )->toHtml();
        return $html;
    }

    /**
     * Retrieve seller validation Url.
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('seller/*/validate', ['_current' => true]);
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $sellerId = $this->getSellerId();
        if (!$sellerId || !$this->sellerAccountManagement->isReadonly($sellerId)) {
            $this->buttonList->add(
                'save_and_continue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                10
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve the save and continue edit Url.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'seller/index/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}']
        );
    }
}
