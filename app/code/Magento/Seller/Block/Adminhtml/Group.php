<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml sellers group page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Seller\Block\Adminhtml;

/**
 * @api
 * @since 100.0.2
 */
class Group extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'seller_group';
        $this->_headerText = __('Seller Groups');
        $this->_addButtonLabel = __('Add New Seller Group');
        parent::_construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'icon-head head-seller-groups';
    }
}
