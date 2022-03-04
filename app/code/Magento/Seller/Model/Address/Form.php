<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Seller Address Form Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Seller\Model\Address;

class Form extends \Magento\Seller\Model\Form
{
    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'seller_address';
}
