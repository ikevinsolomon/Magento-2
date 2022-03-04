<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Controller\RegistryConstants;
use Magento\Seller\Model\Address\Mapper;
use Magento\Framework\Message\Error;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Index
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var \Magento\Framework\Validator
     * @deprecated 101.0.0
     */
    protected $_validator;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Seller\Model\SellerFactory
     * @deprecated 101.0.0
     */
    protected $_sellerFactory = null;

    /**
     * @var \Magento\Seller\Model\AddressFactory
     * @deprecated 101.0.0
     */
    protected $_addressFactory = null;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Seller\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var SellerRepositoryInterface
     */
    protected $_sellerRepository;

    /**
     * @var  \Magento\Seller\Helper\View
     */
    protected $_viewHelper;

    /**
     * @var \Magento\Framework\Math\Random
     * @deprecated 101.0.0
     */
    protected $_random;

    /**
     * @var ObjectFactory
     */
    protected $_objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     * @deprecated 101.0.0
     */
    protected $_extensibleDataObjectConverter;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var SellerInterfaceFactory
     */
    protected $sellerDataFactory;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Seller\Model\Seller\Mapper
     */
    protected $sellerMapper;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 101.0.0
     */
    protected $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @deprecated 101.0.0
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
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
     * @param ObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
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
        ObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_sellerFactory = $sellerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_formFactory = $formFactory;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_viewHelper = $viewHelper;
        $this->_random = $random;
        $this->_sellerRepository = $sellerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->addressMapper = $addressMapper;
        $this->sellerAccountManagement = $sellerAccountManagement;
        $this->addressRepository = $addressRepository;
        $this->sellerDataFactory = $sellerDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->sellerMapper = $sellerMapper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->_objectFactory = $objectFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->layoutFactory = $layoutFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Seller initialization
     *
     * @return string seller id
     */
    protected function initCurrentSeller()
    {
        $sellerId = (int)$this->getRequest()->getParam('id');

        if ($sellerId) {
            $this->_coreRegistry->register(RegistryConstants::CURRENT_SELLER_ID, $sellerId);
        }

        return $sellerId;
    }

    /**
     * Prepare seller default title
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return void
     */
    protected function prepareDefaultSellerTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Sellers'));
    }

    /**
     * Add errors messages to session.
     *
     * @param array|string $messages
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;

        $callback = function ($error) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * Helper function that handles mass actions by taking in a callable for handling a single seller action.
     *
     * @param callable $singleAction A single action callable that takes a seller ID as input
     * @param int[] $sellerIds Array of seller Ids to perform the action upon
     * @return int Number of sellers successfully acted upon
     * @deprecated 101.0.0
     */
    protected function actUponMultipleSellers(callable $singleAction, $sellerIds)
    {
        if (!is_array($sellerIds)) {
            $this->messageManager->addErrorMessage(__('Please select seller(s).'));
            return 0;
        }
        $sellersUpdated = 0;
        foreach ($sellerIds as $sellerId) {
            try {
                $singleAction($sellerId);
                $sellersUpdated++;
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        return $sellersUpdated;
    }
}
