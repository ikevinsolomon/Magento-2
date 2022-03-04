<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Seller address controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Address extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Seller\Model\Session
     */
    protected $_sellerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Seller\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var \Magento\Seller\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Seller\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Seller\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $_dataProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_sellerSession = $sellerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_formFactory = $formFactory;
        $this->_addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->_dataProcessor = $dataProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Retrieve seller session object
     *
     * @return \Magento\Seller\Model\Session
     */
    protected function _getSession()
    {
        return $this->_sellerSession;
    }

    /**
     * Check seller authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create(\Magento\Framework\UrlInterface::class);
        return $urlBuilder->getUrl($route, $params);
    }
}
