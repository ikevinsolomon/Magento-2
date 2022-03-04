<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;

/**
 * Seller front  newsletter manage block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Newsletter extends \Magento\Seller\Block\Account\Dashboard
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Seller::form/newsletter.phtml';

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Return the save action Url.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl('newsletter/manage/save');
    }
}
