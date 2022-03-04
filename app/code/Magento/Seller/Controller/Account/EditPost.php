<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Controller\Account;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\SessionCleanerInterface;
use Magento\Seller\Model\AddressRegistry;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Seller\Model\AuthenticationInterface;
use Magento\Seller\Model\Seller\Mapper;
use Magento\Seller\Model\EmailNotificationInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\SellerExtractor;
use Magento\Seller\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Seller\Controller\AbstractAccount;
use Magento\Framework\Phrase;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Seller edit account information controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * Form code for data extractor
     */
    const FORM_DATA_EXTRACTOR_CODE = 'seller_account_edit';

    /**
     * @var AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var SellerExtractor
     */
    protected $sellerExtractor;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var Mapper
     */
    private $sellerMapper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var SessionCleanerInterface|null
     */
    private $sessionCleaner;

    /**
     * @param Context $context
     * @param Session $sellerSession
     * @param AccountManagementInterface $sellerAccountManagement
     * @param SellerRepositoryInterface $sellerRepository
     * @param Validator $formKeyValidator
     * @param SellerExtractor $sellerExtractor
     * @param Escaper|null $escaper
     * @param AddressRegistry|null $addressRegistry
     * @param Filesystem $filesystem
     * @param SessionCleanerInterface|null $sessionCleaner
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        AccountManagementInterface $sellerAccountManagement,
        SellerRepositoryInterface $sellerRepository,
        Validator $formKeyValidator,
        SellerExtractor $sellerExtractor,
        ?Escaper $escaper = null,
        AddressRegistry $addressRegistry = null,
        Filesystem $filesystem = null,
        ?SessionCleanerInterface $sessionCleaner = null
    ) {
        parent::__construct($context);
        $this->session = $sellerSession;
        $this->sellerAccountManagement = $sellerAccountManagement;
        $this->sellerRepository = $sellerRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->sellerExtractor = $sellerExtractor;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
        $this->addressRegistry = $addressRegistry ?: ObjectManager::getInstance()->get(AddressRegistry::class);
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
        $this->sessionCleaner = $sessionCleaner ?: ObjectManager::getInstance()->get(SessionCleanerInterface::class);
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {

        if (!($this->authentication instanceof AuthenticationInterface)) {
            return ObjectManager::getInstance()->get(AuthenticationInterface::class);
        } else {
            return $this->authentication;
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
            return ObjectManager::getInstance()->get(EmailNotificationInterface::class);
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Change seller email or password action
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $validFormKey = $this->formKeyValidator->validate($this->getRequest());

        if ($validFormKey && $this->getRequest()->isPost()) {
            $currentSellerDataObject = $this->getSellerDataObject($this->session->getSellerId());
            $sellerCandidateDataObject = $this->populateNewSellerDataObject(
                $this->_request,
                $currentSellerDataObject
            );

            $attributeToDelete = $this->_request->getParam('delete_attribute_value');
            if ($attributeToDelete !== null) {
                $this->deleteSellerFileAttribute(
                    $sellerCandidateDataObject,
                    $attributeToDelete
                );
            }

            try {
                // whether a seller enabled change email option
                $this->processChangeEmailRequest($currentSellerDataObject);

                // whether a seller enabled change password option
                $isPasswordChanged = $this->changeSellerPassword($currentSellerDataObject->getEmail());

                // No need to validate seller address while editing seller profile
                $this->disableAddressValidation($sellerCandidateDataObject);

                $this->sellerRepository->save($sellerCandidateDataObject);
                $this->getEmailNotification()->credentialsChanged(
                    $sellerCandidateDataObject,
                    $currentSellerDataObject->getEmail(),
                    $isPasswordChanged
                );
                $this->dispatchSuccessEvent($sellerCandidateDataObject);
                $this->messageManager->addSuccessMessage(__('You saved the account information.'));
                // logout from current session if password changed.
                if ($isPasswordChanged) {
                    $this->session->logout();
                    $this->session->start();
                    return $resultRedirect->setPath('seller/account/login');
                }
                return $resultRedirect->setPath('seller/account');
            } catch (InvalidEmailOrPasswordException $e) {
                $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
            } catch (UserLockedException $e) {
                $message = __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                );
                $this->session->logout();
                $this->session->start();
                $this->messageManager->addErrorMessage($message);

                return $resultRedirect->setPath('seller/account/login');
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
                foreach ($e->getErrors() as $error) {
                    $this->messageManager->addErrorMessage($this->escaper->escapeHtml($error->getMessage()));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the seller.'));
            }

            $this->session->setSellerFormData($this->getRequest()->getPostValue());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/edit');

        return $resultRedirect;
    }

    /**
     * Account editing action completed successfully event
     *
     * @param SellerInterface $sellerCandidateDataObject
     * @return void
     */
    private function dispatchSuccessEvent(SellerInterface $sellerCandidateDataObject)
    {
        $this->_eventManager->dispatch(
            'seller_account_edited',
            ['email' => $sellerCandidateDataObject->getEmail()]
        );
    }

    /**
     * Get seller data object
     *
     * @param int $sellerId
     *
     * @return SellerInterface
     */
    private function getSellerDataObject($sellerId)
    {
        return $this->sellerRepository->getById($sellerId);
    }

    /**
     * Create Data Transfer Object of seller candidate
     *
     * @param RequestInterface $inputData
     * @param SellerInterface $currentSellerData
     * @return SellerInterface
     */
    private function populateNewSellerDataObject(
        RequestInterface $inputData,
        SellerInterface $currentSellerData
    ) {
        $attributeValues = $this->getSellerMapper()->toFlatArray($currentSellerData);
        $sellerDto = $this->sellerExtractor->extract(
            self::FORM_DATA_EXTRACTOR_CODE,
            $inputData,
            $attributeValues
        );
        $sellerDto->setId($currentSellerData->getId());
        if (!$sellerDto->getAddresses()) {
            $sellerDto->setAddresses($currentSellerData->getAddresses());
        }
        if (!$inputData->getParam('change_email')) {
            $sellerDto->setEmail($currentSellerData->getEmail());
        }

        return $sellerDto;
    }

    /**
     * Change seller password
     *
     * @param string $email
     * @return boolean
     * @throws InvalidEmailOrPasswordException|InputException
     */
    protected function changeSellerPassword($email)
    {
        $isPasswordChanged = false;
        if ($this->getRequest()->getParam('change_password')) {
            $currPass = $this->getRequest()->getPost('current_password');
            $newPass = $this->getRequest()->getPost('password');
            $confPass = $this->getRequest()->getPost('password_confirmation');
            if ($newPass != $confPass) {
                throw new InputException(__('Password confirmation doesn\'t match entered password.'));
            }

            $isPasswordChanged = $this->sellerAccountManagement->changePassword($email, $currPass, $newPass);
        }

        return $isPasswordChanged;
    }

    /**
     * Process change email request
     *
     * @param SellerInterface $currentSellerDataObject
     * @return void
     * @throws InvalidEmailOrPasswordException
     * @throws UserLockedException
     */
    private function processChangeEmailRequest(SellerInterface $currentSellerDataObject)
    {
        if ($this->getRequest()->getParam('change_email')) {
            // authenticate user for changing email
            try {
                $this->getAuthentication()->authenticate(
                    $currentSellerDataObject->getId(),
                    $this->getRequest()->getPost('current_password')
                );
                $this->sessionCleaner->clearFor($currentSellerDataObject->getId());
            } catch (InvalidEmailOrPasswordException $e) {
                throw new InvalidEmailOrPasswordException(
                    __("The password doesn't match this account. Verify the password and try again.")
                );
            }
        }
    }

    /**
     * Get Seller Mapper instance
     *
     * @return Mapper
     *
     * @deprecated 100.1.3
     */
    private function getSellerMapper()
    {
        if ($this->sellerMapper === null) {
            $this->sellerMapper = ObjectManager::getInstance()->get(Mapper::class);
        }
        return $this->sellerMapper;
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
     * Removes file attribute from seller entity and file from filesystem
     *
     * @param SellerInterface $sellerCandidateDataObject
     * @param string $attributeToDelete
     * @return void
     */
    private function deleteSellerFileAttribute(
        SellerInterface $sellerCandidateDataObject,
        string $attributeToDelete
    ) : void {
        if ($attributeToDelete !== '') {
            if (strpos($attributeToDelete, ',') !== false) {
                $attributes = explode(',', $attributeToDelete);
            } else {
                $attributes[] = $attributeToDelete;
            }
            foreach ($attributes as $attr) {
                $attributeValue = $sellerCandidateDataObject->getCustomAttribute($attr);
                if ($attributeValue!== null) {
                    if ($attributeValue->getValue() !== '') {
                        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                        $fileName = $attributeValue->getValue();
                        $path = $mediaDirectory->getAbsolutePath('seller' . $fileName);
                        if ($fileName && $mediaDirectory->isFile($path)) {
                            $mediaDirectory->delete($path);
                        }
                        $sellerCandidateDataObject->setCustomAttribute(
                            $attr,
                            ''
                        );
                    }
                }
            }
        }
    }
}
