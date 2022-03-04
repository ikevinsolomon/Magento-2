<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Controller\Adminhtml\Index as BaseAction;
use Magento\Seller\Helper\View;
use Magento\Seller\Model\Address\Mapper;
use Magento\Seller\Model\AddressFactory;
use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Admin seller shopping cart controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 101.0.0
 */
class Cart extends BaseAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param SellerFactory $sellerFactory
     * @param AddressFactory $addressFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param View $viewHelper
     * @param Random $random
     * @param SellerRepositoryInterface $sellerRepository
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $sellerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param SellerInterfaceFactory $sellerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Seller\Model\Seller\Mapper $sellerMapper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param QuoteFactory|null $quoteFactory
     * @param StoreManagerInterface|null $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        SellerFactory $sellerFactory,
        AddressFactory $addressFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        View $viewHelper,
        Random $random,
        SellerRepositoryInterface $sellerRepository,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $sellerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        SellerInterfaceFactory $sellerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Seller\Model\Seller\Mapper $sellerMapper,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $resultJsonFactory,
        QuoteFactory $quoteFactory = null,
        ?StoreManagerInterface $storeManager = null
    ) {
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
        $this->quoteFactory = $quoteFactory ?: $this->_objectManager->get(QuoteFactory::class);
        $this->storeManager = $storeManager ?? $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Handle and then get cart grid contents
     *
     * @return Layout
     */
    public function execute()
    {
        $sellerId = $this->initCurrentSeller();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            /** @var CartRepositoryInterface $quoteRepository */
            $quoteRepository = $this->_objectManager->create(CartRepositoryInterface::class);
            /** @var Quote $quote */
            try {
                $storeIds = $this->storeManager->getWebsite($websiteId)->getStoreIds();
                $quote = $quoteRepository->getForSeller($sellerId, $storeIds);
            } catch (NoSuchEntityException $e) {
                $quote = $this->quoteFactory->create();
            }
            $quote->setWebsite(
                $this->storeManager->getWebsite($websiteId)
            );
            $item = $quote->getItemById($deleteItemId);
            if ($item && $item->getId()) {
                $quote->removeItem($deleteItemId);
                $quoteRepository->save($quote->collectTotals());
            }
        }

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('admin.seller.view.edit.cart')->setWebsiteId($websiteId);
        return $resultLayout;
    }
}
