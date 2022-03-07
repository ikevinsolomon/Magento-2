<?php

namespace Honasa\DisableFrontend\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Backend\Helper\Data;
use Honasa\DisableFrontend\Helper\Data as DisableFrontendHelper;
use Psr\Log\LoggerInterface;

class DisableFrontend implements ObserverInterface
{

    protected $_actionFlag;
    protected $redirect;
    private $helperBackend;
    private $logger;
    private $disableFrontendHelper;

    /**
     * DisableFrontend constructor.
     *
     * @param ActionFlag $actionFlag
     * @param RedirectInterface $redirect
     * @param Data $helperBackend
     * @param DisableFrontendHelper $disableFrontendHelper
     * @param LoggerInterface $logger
     * @author Abel Bolanos Martinez <abelbmartinez@gmail.com>
     */
    public function __construct(
        ActionFlag $actionFlag,
        RedirectInterface $redirect,
        Data $helperBackend,
        DisableFrontendHelper $disableFrontendHelper,
        LoggerInterface $logger
    ) {
        $this->_actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->helperBackend = $helperBackend;
        $this->logger = $logger;
        $this->disableFrontendHelper = $disableFrontendHelper;
    }

    /**
     * Show an empty page(default) or redirect to Admin site.
     * Depend in the config in
     * Stores > Configuration > Advanced > Admin > Disable Frontend
     *
     * @param Observer $observer
     * @return void
     * @author Abel Bolanos Martinez <abelbmartinez@gmail.com>
     */
    public function execute(Observer $observer)
    {

        //$this->logger->info('TEST');

        $this->_actionFlag->set('', Action::FLAG_NO_DISPATCH, true);

        if ($this->disableFrontendHelper->getConfigValue()) {//redirect to Admin
            $controller = $observer->getControllerAction();
            $this->redirect->redirect($controller->getResponse(), $this->helperBackend->getHomePageUrl());
        }
    }
}
