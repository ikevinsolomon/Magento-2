<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class for saving of seller address
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var \Magento\Seller\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Seller\Model\Metadata\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Seller\Api\Data\AddressInterfaceFactory
     */
    private $addressDataFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Seller\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Seller\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Seller\Api\SellerRepositoryInterface $sellerRepository
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Seller\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Seller\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Seller\Model\Metadata\FormFactory $formFactory,
        \Magento\Seller\Api\SellerRepositoryInterface $sellerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Seller\Api\Data\AddressInterfaceFactory $addressDataFactory,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->formFactory = $formFactory;
        $this->sellerRepository = $sellerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressDataFactory = $addressDataFactory;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Save seller address action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $sellerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('entity_id', false);

        $error = false;
        try {
            /** @var \Magento\Seller\Api\Data\SellerInterface $seller */
            $seller = $this->sellerRepository->getById($sellerId);

            $addressForm = $this->formFactory->create(
                'seller_address',
                'adminhtml_seller_address',
                [],
                false,
                false
            );
            $addressData = $addressForm->extractData($this->getRequest());
            $addressData = $addressForm->compactData($addressData);

            $addressData['region'] = [
                'region' => $addressData['region'] ?? null,
                'region_id' => $addressData['region_id'] ?? null,
            ];
            $addressToSave = $this->addressDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $addressToSave,
                $addressData,
                \Magento\Seller\Api\Data\AddressInterface::class
            );
            $addressToSave->setSellerId($seller->getId());
            $addressToSave->setIsDefaultBilling(
                (bool)$this->getRequest()->getParam('default_billing', false)
            );
            $addressToSave->setIsDefaultShipping(
                (bool)$this->getRequest()->getParam('default_shipping', false)
            );
            if ($addressId) {
                $addressToSave->setId($addressId);
                $message = __('Seller address has been updated.');
            } else {
                $addressToSave->setId(null);
                $message = __('New seller address has been added.');
            }
            $savedAddress = $this->addressRepository->save($addressToSave);
            $addressId = $savedAddress->getId();
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
            $error = true;
            $message = __('There is no seller with such id.');
        } catch (LocalizedException $e) {
            $error = true;
            $message = __($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $error = true;
            $message = __('We can\'t change seller address right now.');
            $this->logger->critical($e);
        }

        $addressId = empty($addressId) ? null : $addressId;
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'messages' => $message,
                'error' => $error,
                'data' => [
                    'entity_id' => $addressId
                ]
            ]
        );

        return $resultJson;
    }
}
