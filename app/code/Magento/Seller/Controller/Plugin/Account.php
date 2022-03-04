<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Controller\Plugin;

use Closure;
use Magento\Seller\Controller\AccountInterface;
use Magento\Seller\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Plugin verifies permissions using Action Name against injected (`fontend/di.xml`) rules
 */
class Account
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $allowedActions = [];

    /**
     * @param RequestInterface $request
     * @param Session $sellerSession
     * @param array $allowedActions List of actions that are allowed for not authorized users
     */
    public function __construct(
        RequestInterface $request,
        Session $sellerSession,
        array $allowedActions = []
    ) {
        $this->request = $request;
        $this->session = $sellerSession;
        $this->allowedActions = $allowedActions;
    }

    /**
     * Executes original method if allowed, otherwise - redirects to log in
     *
     * @param AccountInterface $controllerAction
     * @param Closure $proceed
     * @return ResultInterface|ResponseInterface|void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(AccountInterface $controllerAction, Closure $proceed)
    {
        /** @FIXME Move Authentication and redirect out of Session model */
        if ($this->isActionAllowed() || $this->session->authenticate()) {
            return $proceed();
        }
    }

    /**
     * Validates whether currently requested action is one of the allowed
     *
     * @return bool
     */
    private function isActionAllowed(): bool
    {
        $action = strtolower($this->request->getActionName());
        $pattern = '/^(' . implode('|', $this->allowedActions) . ')$/i';

        return (bool)preg_match($pattern, $action);
    }
}
