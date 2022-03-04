<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\AddressMetadataInterface;
use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Seller\Api\Data\AttributeMetadataInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Controller\RegistryConstants;
use Magento\Seller\Helper\View;
use Magento\Seller\Model\Address\Mapper;
use Magento\Seller\Model\AddressFactory;
use Magento\Seller\Model\AddressRegistry;
use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\EmailNotificationInterface;
use Magento\Seller\Model\Metadata\Form;
use Magento\Seller\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;

/**
 * Save seller action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Seller\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

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
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param AddressRegistry|null $addressRegistry
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
        SubscriptionManagerInterface $subscriptionManager,
        AddressRegistry $addressRegistry = null
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
        $this->subscriptionManager = $subscriptionManager;
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
    }

    /**
     * Reformat seller account data to be compatible with seller service interface
     *
     * @return array
     */
    protected function _extractSellerData()
    {
        $sellerData = [];
        if ($this->getRequest()->getPost('seller')) {
            $additionalAttributes = [
                SellerInterface::DEFAULT_BILLING,
                SellerInterface::DEFAULT_SHIPPING,
                'confirmation',
                'sendemail_store_id',
                'extension_attributes',
            ];

            $sellerData = $this->_extractData(
                'adminhtml_seller',
                SellerMetadataInterface::ENTITY_TYPE_SELLER,
                $additionalAttributes,
                'seller'
            );
        }

        if (isset($sellerData['disable_auto_group_change'])) {
            $sellerData['disable_auto_group_change'] = (int) filter_var(
                $sellerData['disable_auto_group_change'],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        return $sellerData;
    }

    /**
     * Perform seller data filtration based on form code and form object
     *
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @return array
     */
    protected function _extractData(
        $formCode,
        $entityType,
        $additionalAttributes = [],
        $scope = null
    ) {
        $metadataForm = $this->getMetadataForm($entityType, $formCode, $scope);
        $formData = $metadataForm->extractData($this->getRequest(), $scope);
        $formData = $metadataForm->compactData($formData);

        // Initialize additional attributes
        /** @var DataObject $object */
        $object = $this->_objectFactory->create(['data' => $this->getRequest()->getPostValue()]);
        $requestData = $object->getData($scope);
        foreach ($additionalAttributes as $attributeCode) {
            $formData[$attributeCode] = isset($requestData[$attributeCode]) ? $requestData[$attributeCode] : false;
        }

        // Unset unused attributes
        $formAttributes = $metadataForm->getAttributes();
        foreach ($formAttributes as $attribute) {
            /** @var AttributeMetadataInterface $attribute */
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getFrontendInput() != 'boolean'
                && $formData[$attributeCode] === false
            ) {
                unset($formData[$attributeCode]);
            }
        }

        if (empty($formData['extension_attributes'])) {
            unset($formData['extension_attributes']);
        }

        return $formData;
    }

    /**
     * Saves default_billing and default_shipping flags for seller address
     *
     * @param array $addressIdList
     * @param array $extractedSellerData
     * @return array
     * @deprecated 102.0.1 must be removed because addresses are save separately for now
     */
    protected function saveDefaultFlags(array $addressIdList, array &$extractedSellerData)
    {
        $result = [];
        $extractedSellerData[SellerInterface::DEFAULT_BILLING] = null;
        $extractedSellerData[SellerInterface::DEFAULT_SHIPPING] = null;
        foreach ($addressIdList as $addressId) {
            $scope = sprintf('address/%s', $addressId);
            $addressData = $this->_extractData(
                'adminhtml_seller_address',
                AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                ['default_billing', 'default_shipping'],
                $scope
            );

            if (is_numeric($addressId)) {
                $addressData['id'] = $addressId;
            }
            // Set default billing and shipping flags to seller
            if (!empty($addressData['default_billing']) && $addressData['default_billing'] === 'true') {
                $extractedSellerData[SellerInterface::DEFAULT_BILLING] = $addressId;
                $addressData['default_billing'] = true;
            } else {
                $addressData['default_billing'] = false;
            }
            if (!empty($addressData['default_shipping']) && $addressData['default_shipping'] === 'true') {
                $extractedSellerData[SellerInterface::DEFAULT_SHIPPING] = $addressId;
                $addressData['default_shipping'] = true;
            } else {
                $addressData['default_shipping'] = false;
            }
            $result[] = $addressData;
        }
        return $result;
    }

    /**
     * Reformat seller addresses data to be compatible with seller service interface
     *
     * @param array $extractedSellerData
     * @return array
     * @deprecated 102.0.1 addresses are saved separately for now
     */
    protected function _extractSellerAddressData(array &$extractedSellerData)
    {
        $addresses = $this->getRequest()->getPost('address');
        $result = [];
        if (is_array($addresses)) {
            if (isset($addresses['_template_'])) {
                unset($addresses['_template_']);
            }

            $addressIdList = array_keys($addresses);
            $result = $this->saveDefaultFlags($addressIdList, $extractedSellerData);
        }

        return $result;
    }

    /**
     * Save seller action
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $returnToEdit = false;
        $sellerId = $this->getCurrentSellerId();
        $seller = $this->sellerDataFactory->create();

        if ($this->getRequest()->getPostValue()) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $sellerData = $this->_extractSellerData();

                if ($sellerId) {
                    $currentSeller = $this->_sellerRepository->getById($sellerId);
                    // No need to validate seller address while editing seller profile
                    $this->disableAddressValidation($currentSeller);
                    $sellerData = array_merge(
                        $this->sellerMapper->toFlatArray($currentSeller),
                        $sellerData
                    );
                    $sellerData['id'] = $sellerId;
                }

                $this->dataObjectHelper->populateWithArray(
                    $seller,
                    $sellerData,
                    SellerInterface::class
                );

                $this->_eventManager->dispatch(
                    'adminhtml_seller_prepare_save',
                    ['seller' => $seller, 'request' => $this->getRequest()]
                );

                if (isset($sellerData['sendemail_store_id']) && $sellerData['sendemail_store_id'] !== false) {
                    $seller->setStoreId($sellerData['sendemail_store_id']);
                    try {
                        $this->sellerAccountManagement->validateSellerStoreIdByWebsiteId($seller);
                    } catch (LocalizedException $exception) {
                        throw new LocalizedException(__("The Store View selected for sending Welcome email from" .
                            " is not related to the seller's associated website."));
                    }
                }

                // Save seller
                if ($sellerId) {
                    $this->_sellerRepository->save($seller);
                    $this->getEmailNotification()->credentialsChanged($seller, $currentSeller->getEmail());
                } else {
                    $seller = $this->sellerAccountManagement->createAccount($seller);
                    $sellerId = $seller->getId();
                }

                $this->updateSubscriptions($seller);

                // After save
                $this->_eventManager->dispatch(
                    'adminhtml_seller_save_after',
                    ['seller' => $seller, 'request' => $this->getRequest()]
                );
                $this->_getSession()->unsSellerFormData();
                // Done Saving seller, finish save action
                $this->_coreRegistry->register(RegistryConstants::CURRENT_SELLER_ID, $sellerId);
                $this->messageManager->addSuccessMessage(__('You saved the seller.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the seller.')
                );
                $returnToEdit = false;
            } catch (Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setSellerFormData($this->retrieveFormattedFormData($seller));
                $returnToEdit = true;
            } catch (AbstractAggregateException $exception) {
                $errors = $exception->getErrors();
                $messages = [];
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setSellerFormData($this->retrieveFormattedFormData($seller));
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $this->_getSession()->setSellerFormData($this->retrieveFormattedFormData($seller));
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the seller.')
                );
                $this->_getSession()->setSellerFormData($this->retrieveFormattedFormData($seller));
                $returnToEdit = true;
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($sellerId) {
                $resultRedirect->setPath(
                    'seller/*/edit',
                    ['id' => $sellerId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    'seller/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('seller/index');
        }
        return $resultRedirect;
    }

    /**
     * Update seller website subscriptions
     *
     * @param SellerInterface $seller
     * @return void
     */
    private function updateSubscriptions(SellerInterface $seller): void
    {
        if (!$this->_authorization->isAllowed(null)) {
            return;
        }

        $subscriptionStatus = (array)$this->getRequest()->getParam('subscription_status');
        $subscriptionStore = (array)$this->getRequest()->getParam('subscription_store');
        if (empty($subscriptionStatus)) {
            return;
        }

        foreach ($subscriptionStatus as $websiteId => $status) {
            $storeId = $subscriptionStore[$websiteId] ?? $seller->getStoreId();
            if ($status) {
                $this->subscriptionManager->subscribeSeller((int)$seller->getId(), $storeId);
            } else {
                $this->subscriptionManager->unsubscribeSeller((int)$seller->getId(), $storeId);
            }
        }
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
            return ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Get metadata form
     *
     * @param string $entityType
     * @param string $formCode
     * @param string $scope
     * @return Form
     */
    private function getMetadataForm($entityType, $formCode, $scope)
    {
        $attributeValues = [];

        if ($entityType == SellerMetadataInterface::ENTITY_TYPE_SELLER) {
            $sellerId = $this->getCurrentSellerId();
            if ($sellerId) {
                $seller = $this->_sellerRepository->getById($sellerId);
                $attributeValues = $this->sellerMapper->toFlatArray($seller);
            }
        }

        if ($entityType == AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
            $scopeData = explode('/', $scope);
            if (isset($scopeData[1]) && is_numeric($scopeData[1])) {
                $sellerAddress = $this->addressRepository->getById($scopeData[1]);
                $attributeValues = $this->addressMapper->toFlatArray($sellerAddress);
            }
        }

        $metadataForm = $this->_formFactory->create(
            $entityType,
            $formCode,
            $attributeValues,
            false,
            Form::DONT_IGNORE_INVISIBLE
        );

        return $metadataForm;
    }

    /**
     * Retrieve current seller ID
     *
     * @return int
     */
    private function getCurrentSellerId()
    {
        $originalRequestData = $this->getRequest()->getPostValue(SellerMetadataInterface::ENTITY_TYPE_SELLER);

        $sellerId = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $sellerId;
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

    /**
     * Retrieve formatted form data
     *
     * @param SellerInterface $seller
     * @return array
     */
    private function retrieveFormattedFormData(SellerInterface $seller): array
    {
        $originalRequestData = $this->getRequest()->getPostValue();
        $sellerData = $this->sellerMapper->toFlatArray($seller);

        /* Seller data filtration */
        if (isset($originalRequestData['seller'])) {
            $sellerData = array_intersect_key($sellerData, $originalRequestData['seller']);
            $originalRequestData['seller'] = array_merge($originalRequestData['seller'], $sellerData);
        }

        return $originalRequestData;
    }
}
