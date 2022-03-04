<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Helper\Session;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Model\Session as SellerSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\LayoutInterface;

/**
 * Class CurrentSeller
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CurrentSeller
{
    /**
     * @var \Magento\Seller\Model\Session
     */
    protected $sellerSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Seller\Api\Data\SellerInterfaceFactory
     */
    protected $sellerFactory;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @param SellerSession $sellerSession
     * @param LayoutInterface $layout
     * @param SellerInterfaceFactory $sellerFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param RequestInterface $request
     * @param ModuleManager $moduleManager
     * @param ViewInterface $view
     */
    public function __construct(
        SellerSession $sellerSession,
        LayoutInterface $layout,
        SellerInterfaceFactory $sellerFactory,
        SellerRepositoryInterface $sellerRepository,
        RequestInterface $request,
        ModuleManager $moduleManager,
        ViewInterface $view
    ) {
        $this->sellerSession = $sellerSession;
        $this->layout = $layout;
        $this->sellerFactory = $sellerFactory;
        $this->sellerRepository = $sellerRepository;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->view = $view;
    }

    /**
     * Returns seller Data with seller group only
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     */
    protected function getDepersonalizedSeller()
    {
        $seller = $this->sellerFactory->create();
        $seller->setGroupId($this->sellerSession->getSellerGroupId());
        return $seller;
    }

    /**
     * Returns seller Data from service
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     */
    protected function getSellerFromService()
    {
        return $this->sellerRepository->getById($this->sellerSession->getId());
    }

    /**
     * Returns current seller according to session and context
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     */
    public function getSeller()
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && !$this->request->isAjax()
            && $this->view->isLayoutLoaded()
            && $this->layout->isCacheable()
        ) {
            return $this->getDepersonalizedSeller();
        } else {
            return $this->getSellerFromService();
        }
    }

    /**
     * Returns seller id from session
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->sellerSession->getId();
    }

    /**
     * Set seller id
     *
     * @param int|null $sellerId
     * @return void
     */
    public function setSellerId($sellerId)
    {
        $this->sellerSession->setId($sellerId);
    }
}
