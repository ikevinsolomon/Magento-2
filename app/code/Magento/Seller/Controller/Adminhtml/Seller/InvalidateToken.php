<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Controller\Adminhtml\Seller;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Integration\Api\SellerTokenServiceInterface;
use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Model\Address\Mapper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class to invalidate tokens for sellers
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class InvalidateToken extends \Magento\Seller\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::invalidate_tokens';

    /**
     * @var SellerTokenServiceInterface
     */
    protected $tokenService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Seller\Model\SellerFactory $sellerFactory
     * @param \Magento\Seller\Model\AddressFactory $addressFactory
     * @param \Magento\Seller\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Seller\Helper\View $viewHelper
     * @param \Magento\Framework\Math\Random $random
     * @param SellerRepositoryInterface $sellerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $sellerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param SellerInterfaceFactory $sellerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Seller\Model\Seller\Mapper $sellerMapper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param SellerTokenServiceInterface $tokenService
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Seller\Model\SellerFactory $sellerFactory,
        \Magento\Seller\Model\AddressFactory $addressFactory,
        \Magento\Seller\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Seller\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        SellerRepositoryInterface $sellerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $sellerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        SellerInterfaceFactory $sellerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Seller\Model\Seller\Mapper $sellerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        DataObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        SellerTokenServiceInterface $tokenService
    ) {
        $this->tokenService = $tokenService;
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $sellerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $sellerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $sellerAccountManagement,
            $addressRepository,
            $sellerDataFactory,
            $addressDataFactory,
            $sellerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
    }

    /**
     * Reset seller's tokens handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($sellerId = $this->getRequest()->getParam('seller_id')) {
            try {
                $this->tokenService->revokeSellerAccessToken($sellerId);
                $this->messageManager->addSuccessMessage(__('You have revoked the seller\'s tokens.'));
                $resultRedirect->setPath('seller/index/edit', ['id' => $sellerId, '_current' => true]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('seller/index/edit', ['id' => $sellerId, '_current' => true]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a seller to revoke.'));
            $resultRedirect->setPath('seller/index/index');
        }
        return $resultRedirect;
    }
}
