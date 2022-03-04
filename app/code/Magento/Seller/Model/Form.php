<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Seller Form Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Seller\Model;

class Form extends \Magento\Eav\Model\Form
{
    /**
     * XML configuration paths for "Disable autocomplete on storefront" property
     */
    const XML_PATH_ENABLE_AUTOCOMPLETE = 'seller/password/autocomplete_on_storefront';

    /**
     * Current module pathname
     *
     * @var string
     */
    protected $_moduleName = 'Magento_Seller';

    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'seller';

    /**
     * Get EAV Entity Form Attribute Collection for Seller
     * exclude 'created_at'
     *
     * @return \Magento\Seller\Model\ResourceModel\Form\Attribute\Collection
     */
    protected function _getFormAttributeCollection()
    {
        return parent::_getFormAttributeCollection()->addFieldToFilter('attribute_code', ['neq' => 'created_at']);
    }
}
