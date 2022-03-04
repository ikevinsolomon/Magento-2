<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account;

use Magento\Seller\Model\Url;
use Magento\Framework\View\Element\Template;

/**
 * Seller account navigation sidebar
 *
 * @api
 * @since 100.0.2
 */
class Forgotpassword extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Url
     */
    protected $sellerUrl;

    /**
     * @param Template\Context $context
     * @param Url $sellerUrl
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $sellerUrl,
        array $data = []
    ) {
        $this->sellerUrl = $sellerUrl;
        parent::__construct($context, $data);
    }

    /**
     * Get login URL
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->sellerUrl->getLoginUrl();
    }
}
