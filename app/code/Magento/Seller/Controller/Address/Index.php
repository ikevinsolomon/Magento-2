<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Address;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Seller\Api\SellerRepositoryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends \Magento\Seller\Controller\Address implements HttpGetActionInterface
{
    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Seller\Model\Session $sellerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Seller\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Seller\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Seller\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Seller\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Seller\Model\Session $sellerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Seller\Model\Metadata\FormFactory $formFactory,
        \Magento\Seller\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Seller\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Seller\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        SellerRepositoryInterface $sellerRepository
    ) {
        $this->sellerRepository = $sellerRepository;
        parent::__construct(
            $context,
            $sellerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory
        );
    }

    /**
     * Seller addresses list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $addresses = $this->sellerRepository->getById($this->_getSession()->getSellerId())->getAddresses();
        if (count($addresses)) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $block = $resultPage->getLayout()->getBlock('address_book');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath('*/*/new');
        }
    }
}
