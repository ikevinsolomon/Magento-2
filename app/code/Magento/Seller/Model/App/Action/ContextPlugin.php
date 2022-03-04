<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\App\Action;

use Magento\Seller\Model\Context;
use Magento\Seller\Model\GroupManagement;
use Magento\Seller\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Introduces Context information for ActionInterface of Seller Action
 */
class ContextPlugin
{
    /**
     * @var Session
     */
    protected $sellerSession;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param Session $sellerSession
     * @param HttpContext $httpContext
     */
    public function __construct(Session $sellerSession, HttpContext $httpContext)
    {
        $this->sellerSession = $sellerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Set seller group and seller session id to HTTP context
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $this->httpContext->setValue(
            Context::CONTEXT_GROUP,
            $this->sellerSession->getSellerGroupId(),
            GroupManagement::NOT_LOGGED_IN_ID
        );
        $this->httpContext->setValue(
            Context::CONTEXT_AUTH,
            $this->sellerSession->isLoggedIn(),
            false
        );
    }
}
