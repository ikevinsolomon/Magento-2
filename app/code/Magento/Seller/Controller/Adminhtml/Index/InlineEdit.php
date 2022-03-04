<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Model\AddressRegistry;
use Magento\Seller\Model\EmailNotificationInterface;
use Magento\Seller\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Seller inline edit action
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var \Magento\Seller\Api\Data\SellerInterface
     */
    private $seller;

    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Seller\Model\Seller\Mapper
     */
    protected $sellerMapper;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Seller\Model\EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param Action\Context $context
     * @param SellerRepositoryInterface $sellerRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Seller\Model\Seller\Mapper $sellerMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param AddressRegistry|null $addressRegistry
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        Action\Context $context,
        SellerRepositoryInterface $sellerRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Seller\Model\Seller\Mapper $sellerMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Psr\Log\LoggerInterface $logger,
        AddressRegistry $addressRegistry = null,
        \Magento\Framework\Escaper $escaper = null
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sellerMapper = $sellerMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        parent::__construct($context);
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     * @deprecated 100.1.0
     */
    private function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Inline edit action execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [
                        __('Please correct the data sent.')
                    ],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($postItems) as $sellerId) {
            $this->setSeller($this->sellerRepository->getById($sellerId));
            $currentSeller = clone $this->getSeller();

            if ($this->getSeller()->getDefaultBilling()) {
                $this->updateDefaultBilling($this->getData($postItems[$sellerId]));
            }
            $this->updateSeller($this->getData($postItems[$sellerId], true));
            $this->saveSeller($this->getSeller());

            $this->getEmailNotification()->credentialsChanged($this->getSeller(), $currentSeller->getEmail());
        }

        return $resultJson->setData(
            [
                'messages' => $this->getErrorMessages(),
                'error' => $this->isErrorExists()
            ]
        );
    }

    /**
     * Receive entity(seller|seller_address) data from request
     *
     * @param array $data
     * @param mixed $isSellerData
     * @return array
     */
    protected function getData(array $data, $isSellerData = null)
    {
        $addressKeys = preg_grep(
            '/^(' . AttributeRepository::BILLING_ADDRESS_PREFIX . '\w+)/',
            array_keys($data),
            $isSellerData
        );
        $result = array_intersect_key($data, array_flip($addressKeys));
        if ($isSellerData === null) {
            foreach ($result as $key => $value) {
                if (strpos($key, AttributeRepository::BILLING_ADDRESS_PREFIX) !== false) {
                    unset($result[$key]);
                    $result[str_replace(AttributeRepository::BILLING_ADDRESS_PREFIX, '', $key)] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Update seller data
     *
     * @param array $data
     * @return void
     */
    protected function updateSeller(array $data)
    {
        $seller = $this->getSeller();
        $sellerData = array_merge(
            $this->sellerMapper->toFlatArray($seller),
            $data
        );
        $this->dataObjectHelper->populateWithArray(
            $seller,
            $sellerData,
            \Magento\Seller\Api\Data\SellerInterface::class
        );
    }

    /**
     * Update seller address data
     *
     * @param array $data
     * @return void
     */
    protected function updateDefaultBilling(array $data)
    {
        $addresses = $this->getSeller()->getAddresses();
        /** @var \Magento\Seller\Api\Data\AddressInterface $address */
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $this->dataObjectHelper->populateWithArray(
                    $address,
                    $this->processAddressData($data),
                    \Magento\Seller\Api\Data\AddressInterface::class
                );
                break;
            }
        }
    }

    /**
     * Save seller with error catching
     *
     * @param SellerInterface $seller
     * @return void
     */
    protected function saveSeller(SellerInterface $seller)
    {
        try {
            // No need to validate seller address during inline edit action
            $this->disableAddressValidation($seller);
            $this->sellerRepository->save($seller);
        } catch (\Magento\Framework\Exception\InputException $e) {
            $this->getMessageManager()
                ->addError($this->getErrorWithSellerId($this->escaper->escapeHtml($e->getMessage())));
            $this->logger->critical($e);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()
                ->addError($this->getErrorWithSellerId($this->escaper->escapeHtml($e->getMessage())));
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->getMessageManager()
                ->addError($this->getErrorWithSellerId('We can\'t save the seller.'));
            $this->logger->critical($e);
        }
    }

    /**
     * Parse street field
     *
     * @param array $data
     * @return array
     */
    protected function processAddressData(array $data)
    {
        foreach (['firstname', 'lastname'] as $requiredField) {
            if (empty($data[$requiredField])) {
                $data[$requiredField] =  $this->getSeller()->{'get' . ucfirst($requiredField)}();
            }
        }
        return $data;
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getErrors() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCountByType(MessageInterface::TYPE_ERROR);
    }

    /**
     * Set seller
     *
     * @param SellerInterface $seller
     * @return $this
     */
    protected function setSeller(SellerInterface $seller)
    {
        $this->seller = $seller;
        return $this;
    }

    /**
     * Receive seller
     *
     * @return SellerInterface
     */
    protected function getSeller()
    {
        return $this->seller;
    }

    /**
     * Add page title to error message
     *
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithSellerId($errorText)
    {
        return '[Seller ID: ' . $this->getSeller()->getId() . '] ' . __($errorText);
    }

    /**
     * Disable Seller Address Validation
     *
     * @param SellerInterface $seller
     * @throws NoSuchEntityException
     */
    private function disableAddressValidation($seller)
    {
        foreach ($seller->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
}
