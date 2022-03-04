<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Account;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Seller\Controller\AbstractAccount implements HttpGetActionInterface
{
    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Session $sellerSession
     * @param PageFactory $resultPageFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        PageFactory $resultPageFactory,
        SellerRepositoryInterface $sellerRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->session = $sellerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->sellerRepository = $sellerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context);
    }

    /**
     * Forgot seller account information page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $block = $resultPage->getLayout()->getBlock('seller_edit');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }

        $data = $this->session->getSellerFormData(true);
        $sellerId = $this->session->getSellerId();
        $sellerDataObject = $this->sellerRepository->getById($sellerId);
        if (!empty($data)) {
            $this->dataObjectHelper->populateWithArray(
                $sellerDataObject,
                $data,
                \Magento\Seller\Api\Data\SellerInterface::class
            );
        }

        $this->session->setSellerData($sellerDataObject);
        $this->session->setChangePassword($this->getRequest()->getParam('changepass') == 1);

        return $resultPage;
    }
}
