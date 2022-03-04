<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account;

use Magento\Seller\Api\SellerRepositoryInterface;

/**
 * @api
 * @since 100.0.2
 */
class Seller extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var \Magento\Seller\Helper\View
     */
    protected $_viewHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * Checking seller login status
     *
     * @return bool
     */
    public function sellerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Seller\Model\Context::CONTEXT_AUTH);
    }
}
