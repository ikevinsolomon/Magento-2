<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Seller\Api\SessionCleanerInterface;
use Magento\Seller\Helper\View as SellerViewHelper;
use Magento\Seller\Model\Config\Share as ConfigShare;
use Magento\Seller\Model\Seller as SellerModel;
use Magento\Seller\Model\Seller\CredentialsValidator;
use Magento\Seller\Model\ForgotPasswordToken\GetSellerByToken;
use Magento\Seller\Model\Metadata\Validator;
use Magento\Seller\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\AuthorizationInterface;

/**
 * Handle various seller account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountManagement implements AccountManagementInterface
{
    /**
     * Configuration paths for create account email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'seller/create_account/email_template';

    /**
     * Configuration paths for register no password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'seller/create_account/email_no_password_template';

    /**
     * Configuration paths for remind email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'seller/create_account/email_identity';

    /**
     * Configuration paths for remind email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'seller/password/remind_email_template';

    /**
     * Configuration paths for forgot email email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'seller/password/forgot_email_template';

    /**
     * Configuration paths for forgot email identity
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'seller/password/forgot_email_identity';

    /**
     * Configuration paths for account confirmation required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see AccountConfirmation::XML_PATH_IS_CONFIRM
     */
    const XML_PATH_IS_CONFIRM = 'seller/create_account/confirm';

    /**
     * Configuration paths for account confirmation email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'seller/create_account/email_confirmation_template';

    /**
     * Configuration paths for confirmation confirmed email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'seller/create_account/email_confirmed_template';

    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Constants for types of emails to send out.
     * pdl:
     * forgot, remind, reset email templates
     */
    const EMAIL_REMINDER = 'email_reminder';

    const EMAIL_RESET = 'email_reset';

    /**
     * Configuration path to seller password minimum length
     */
    const XML_PATH_MINIMUM_PASSWORD_LENGTH = 'seller/password/minimum_password_length';

    /**
     * Configuration path to seller password required character classes number
     */
    const XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER = 'seller/password/required_character_classes_number';

    /**
     * Configuration path to seller reset password email template
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see Magento/Seller/Model/EmailNotification::XML_PATH_REGISTER_EMAIL_TEMPLATE
     */
    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'seller/password/reset_password_template';

    /**
     * Minimum password length
     *
     * @deprecated Get rid of Helpers in Password Security Management
     * @see \Magento\Seller\Model\AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var SellerFactory
     */
    private $sellerFactory;

    /**
     * @var \Magento\Seller\Api\Data\ValidationResultsInterfaceFactory
     */
    private $validationResultsDataFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var SellerMetadataInterface
     */
    private $sellerMetadataService;

    /**
     * @var PsrLogger
     */
    protected $logger;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var SellerRegistry
     */
    private $sellerRegistry;

    /**
     * @var ConfigShare
     */
    private $configShare;

    /**
     * @var StringHelper
     */
    protected $stringHelper;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var SellerViewHelper
     */
    protected $sellerViewHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var SellerModel
     */
    protected $sellerModel;

    /**
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var \Magento\Eav\Model\Validator\Attribute\Backend
     */
    private $eavValidator;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AddressRegistry
     */
    private $addressRegistry;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @var GetSellerByToken
     */
    private $getByToken;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param SellerFactory $sellerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param SellerMetadataInterface $sellerMetadataService
     * @param SellerRegistry $sellerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param SellerRepositoryInterface $sellerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param SellerViewHelper $sellerViewHelper
     * @param DateTime $dateTime
     * @param SellerModel $sellerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param CredentialsValidator|null $credentialsValidator
     * @param DateTimeFactory|null $dateTimeFactory
     * @param AccountConfirmation|null $accountConfirmation
     * @param SessionManagerInterface|null $sessionManager
     * @param SaveHandlerInterface|null $saveHandler
     * @param CollectionFactory|null $visitorCollectionFactory
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param AddressRegistry|null $addressRegistry
     * @param GetSellerByToken|null $getByToken
     * @param AllowedCountries|null $allowedCountriesReader
     * @param SessionCleanerInterface|null $sessionCleaner
     * @param AuthorizationInterface|null $authorization
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        SellerFactory $sellerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        SellerMetadataInterface $sellerMetadataService,
        SellerRegistry $sellerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        SellerRepositoryInterface $sellerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        SellerViewHelper $sellerViewHelper,
        DateTime $dateTime,
        SellerModel $sellerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        AddressRegistry $addressRegistry = null,
        GetSellerByToken $getByToken = null,
        AllowedCountries $allowedCountriesReader = null,
        SessionCleanerInterface $sessionCleaner = null,
        AuthorizationInterface $authorization = null
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->sellerMetadataService = $sellerMetadataService;
        $this->sellerRegistry = $sellerRegistry;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->sellerRepository = $sellerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->sellerViewHelper = $sellerViewHelper;
        $this->dateTime = $dateTime;
        $this->sellerModel = $sellerModel;
        $this->objectFactory = $objectFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $objectManager = ObjectManager::getInstance();
        $this->credentialsValidator =
            $credentialsValidator ?: $objectManager->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->accountConfirmation = $accountConfirmation ?: $objectManager
            ->get(AccountConfirmation::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: $objectManager->get(SearchCriteriaBuilder::class);
        $this->addressRegistry = $addressRegistry
            ?: $objectManager->get(AddressRegistry::class);
        $this->getByToken = $getByToken
            ?: $objectManager->get(GetSellerByToken::class);
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: $objectManager->get(AllowedCountries::class);
        $this->sessionCleaner = $sessionCleaner ?? $objectManager->get(SessionCleanerInterface::class);
        $this->authorization = $authorization ?? $objectManager->get(AuthorizationInterface::class);
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Seller\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * @inheritdoc
     */
    public function resendConfirmation($email, $websiteId = null, $redirectUrl = '')
    {
        $seller = $this->sellerRepository->get($email, $websiteId);
        if (!$seller->getConfirmation()) {
            throw new InvalidTransitionException(__("Confirmation isn't needed."));
        }

        try {
            $this->getEmailNotification()->newAccount(
                $seller,
                self::NEW_ACCOUNT_EMAIL_CONFIRMATION,
                $redirectUrl,
                $this->storeManager->getStore()->getId()
            );
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function activate($email, $confirmationKey)
    {
        $seller = $this->sellerRepository->get($email);
        return $this->activateSeller($seller, $confirmationKey);
    }

    /**
     * @inheritdoc
     */
    public function activateById($sellerId, $confirmationKey)
    {
        $seller = $this->sellerRepository->getById($sellerId);
        return $this->activateSeller($seller, $confirmationKey);
    }

    /**
     * Activate a seller account using a key that was sent in a confirmation email.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @param string $confirmationKey
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidTransitionException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function activateSeller($seller, $confirmationKey)
    {
        // check if seller is inactive
        if (!$seller->getConfirmation()) {
            throw new InvalidTransitionException(__('The account is already active.'));
        }

        if ($seller->getConfirmation() !== $confirmationKey) {
            throw new InputMismatchException(__('The confirmation token is invalid. Verify the token and try again.'));
        }

        $seller->setConfirmation(null);
        // No need to validate seller and seller address while activating seller
        $this->setIgnoreValidationFlag($seller);
        $this->sellerRepository->save($seller);
        $this->getEmailNotification()->newAccount(
            $seller,
            'confirmed',
            '',
            $this->storeManager->getStore()->getId()
        );
        return $seller;
    }

    /**
     * @inheritdoc
     */
    public function authenticate($username, $password)
    {
        try {
            $seller = $this->sellerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        $sellerId = $seller->getId();
        if ($this->getAuthentication()->isLocked($sellerId)) {
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->getAuthentication()->authenticate($sellerId, $password);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($seller->getConfirmation() && $this->isConfirmationRequired($seller)) {
            throw new EmailNotConfirmedException(__("This account isn't confirmed. Verify and try again."));
        }

        $sellerModel = $this->sellerFactory->create()->updateData($seller);
        $this->eventManager->dispatch(
            'seller_seller_authenticated',
            ['model' => $sellerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('seller_data_object_login', ['seller' => $seller]);

        return $seller;
    }

    /**
     * @inheritdoc
     */
    public function validateResetPasswordLinkToken($sellerId, $resetPasswordLinkToken)
    {
        $this->validateResetPasswordToken($sellerId, $resetPasswordLinkToken);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        // load seller by email
        $seller = $this->sellerRepository->get($email, $websiteId);

        // No need to validate seller address while saving seller reset password token
        $this->disableAddressValidation($seller);

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($seller, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->getEmailNotification()->passwordReminder($seller);
                    break;
                case AccountManagement::EMAIL_RESET:
                    $this->getEmailNotification()->passwordResetConfirmation($seller);
                    break;
                default:
                    $this->handleUnknownTemplate($template);
                    break;
            }
            return true;
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * Handle not supported template
     *
     * @param string $template
     * @throws InputException
     */
    private function handleUnknownTemplate($template)
    {
        throw new InputException(
            __(
                'Invalid value of "%value" provided for the %fieldName field. '
                    . 'Possible values: %template1 or %template2.',
                [
                    'value' => $template,
                    'fieldName' => 'template',
                    'template1' => AccountManagement::EMAIL_REMINDER,
                    'template2' => AccountManagement::EMAIL_RESET
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function resetPassword($email, $resetToken, $newPassword)
    {
        if (!$email) {
            $seller = $this->getByToken->execute($resetToken);
            $email = $seller->getEmail();
        } else {
            $seller = $this->sellerRepository->get($email);
        }

        // No need to validate seller and seller address while saving seller reset password token
        $this->disableAddressValidation($seller);
        $this->setIgnoreValidationFlag($seller);

        //Validate Token and new password strength
        $this->validateResetPasswordToken($seller->getId(), $resetToken);
        $this->credentialsValidator->checkPasswordDifferentFromEmail(
            $email,
            $newPassword
        );
        $this->checkPasswordStrength($newPassword);
        //Update secure data
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($seller->getId());
        $sellerSecure->setRpToken(null);
        $sellerSecure->setRpTokenCreatedAt(null);
        $sellerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->sessionCleaner->clearFor((int)$seller->getId());
        $this->sellerRepository->save($seller);

        return true;
    }

    /**
     * Make sure that password complies with minimum security requirements.
     *
     * @param string $password
     * @return void
     * @throws InputException
     */
    protected function checkPasswordStrength($password)
    {
        $length = $this->stringHelper->strlen($password);
        if ($length > self::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    self::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'The password needs at least %1 characters. Create a new password and try again.',
                    $configMinPasswordLength
                )
            );
        }
        if ($this->stringHelper->strlen(trim($password)) != $length) {
            throw new InputException(
                __("The password can't begin or end with a space. Verify the password and try again.")
            );
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }

    /**
     * Check password for presence of required character sets
     *
     * @param string $password
     * @return int
     */
    protected function makeRequiredCharactersCheck($password)
    {
        $counter = 0;
        $requiredNumber = $this->scopeConfig->getValue(self::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
        $return = 0;

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }

    /**
     * Retrieve minimum password length
     *
     * @return int
     */
    protected function getMinPasswordLength()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * @inheritdoc
     */
    public function getConfirmationStatus($sellerId)
    {
        // load seller by id
        $seller = $this->sellerRepository->getById($sellerId);
        if ($this->isConfirmationRequired($seller)) {
            if (!$seller->getConfirmation()) {
                return self::ACCOUNT_CONFIRMED;
            }
            return self::ACCOUNT_CONFIRMATION_REQUIRED;
        }
        return self::ACCOUNT_CONFIRMATION_NOT_REQUIRED;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function createAccount(SellerInterface $seller, $password = null, $redirectUrl = '')
    {
        $groupId = $seller->getGroupId();
        if (isset($groupId) && !$this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
            $seller->setGroupId(null);
        }

        if ($password !== null) {
            $this->checkPasswordStrength($password);
            $sellerEmail = $seller->getEmail();
            try {
                $this->credentialsValidator->checkPasswordDifferentFromEmail($sellerEmail, $password);
            } catch (InputException $e) {
                throw new LocalizedException(
                    __("The password can't be the same as the email address. Create a new password and try again.")
                );
            }
            $hash = $this->createPasswordHash($password);
        } else {
            $hash = null;
        }
        return $this->createAccountWithPasswordHash($seller, $hash, $redirectUrl);
    }

    /**
     * @inheritdoc
     *
     * @throws InputMismatchException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function createAccountWithPasswordHash(SellerInterface $seller, $hash, $redirectUrl = '')
    {
        // This logic allows an existing seller to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($seller->getId()) {
            $seller = $this->sellerRepository->get($seller->getEmail());
            $websiteId = $seller->getWebsiteId();

            if ($this->isSellerInStore($websiteId, $seller->getStoreId())) {
                throw new InputException(__('This seller already exists in this store.'));
            }
            // Existing password hash will be used from secured seller data registry when saving seller
        }

        // Make sure we have a storeId to associate this seller with.
        if (!$seller->getStoreId()) {
            if ($seller->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($seller->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $this->storeManager->setCurrentStore(null);
                $storeId = $this->storeManager->getStore()->getId();
            }
            $seller->setStoreId($storeId);
        }

        // Associate website_id with seller
        if (!$seller->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($seller->getStoreId())->getWebsiteId();
            $seller->setWebsiteId($websiteId);
        }

        $this->validateSellerStoreIdByWebsiteId($seller);

        // Update 'created_in' value with actual store name
        if ($seller->getId() === null) {
            $storeName = $this->storeManager->getStore($seller->getStoreId())->getName();
            $seller->setCreatedIn($storeName);
        }

        $sellerAddresses = $seller->getAddresses() ?: [];
        $seller->setAddresses(null);
        try {
            // If seller exists existing hash will be used by Repository
            $seller = $this->sellerRepository->save($seller, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A seller with the same email address already exists in an associated website.')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($sellerAddresses as $address) {
                if (!$this->isAddressAllowedForWebsite($address, $seller->getStoreId())) {
                    continue;
                }
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setSellerId($seller->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setSellerId($seller->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->sellerRegistry->remove($seller->getId());
        } catch (InputException $e) {
            $this->sellerRepository->delete($seller);
            throw $e;
        }
        $seller = $this->sellerRepository->getById($seller->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($seller, $newLinkToken);
        $this->sendEmailConfirmation($seller, $redirectUrl);

        return $seller;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultBillingAddress($sellerId)
    {
        $seller = $this->sellerRepository->getById($sellerId);
        return $this->getAddressById($seller, $seller->getDefaultBilling());
    }

    /**
     * @inheritdoc
     */
    public function getDefaultShippingAddress($sellerId)
    {
        $seller = $this->sellerRepository->getById($sellerId);
        return $this->getAddressById($seller, $seller->getDefaultShipping());
    }

    /**
     * Send either confirmation or welcome email after an account creation
     *
     * @param SellerInterface $seller
     * @param string $redirectUrl
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function sendEmailConfirmation(SellerInterface $seller, $redirectUrl)
    {
        try {
            $hash = $this->sellerRegistry->retrieveSecureData($seller->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            if ($this->isConfirmationRequired($seller) && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $this->getEmailNotification()->newAccount($seller, $templateType, $redirectUrl, $seller->getStoreId());
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e);
        }
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidEmailOrPasswordException
     */
    public function changePassword($email, $currentPassword, $newPassword)
    {
        try {
            $seller = $this->sellerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForSeller($seller, $currentPassword, $newPassword);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidEmailOrPasswordException
     */
    public function changePasswordById($sellerId, $currentPassword, $newPassword)
    {
        try {
            $seller = $this->sellerRepository->getById($sellerId);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return $this->changePasswordForSeller($seller, $currentPassword, $newPassword);
    }

    /**
     * Change seller password
     *
     * @param SellerInterface $seller
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws InputException
     * @throws InputMismatchException
     * @throws InvalidEmailOrPasswordException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UserLockedException
     */
    private function changePasswordForSeller($seller, $currentPassword, $newPassword)
    {
        try {
            $this->getAuthentication()->authenticate($seller->getId(), $currentPassword);
        } catch (InvalidEmailOrPasswordException $e) {
            throw new InvalidEmailOrPasswordException(
                __("The password doesn't match this account. Verify the password and try again.")
            );
        }
        $sellerEmail = $seller->getEmail();
        $this->credentialsValidator->checkPasswordDifferentFromEmail($sellerEmail, $newPassword);
        $this->checkPasswordStrength($newPassword);
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($seller->getId());
        $sellerSecure->setRpToken(null);
        $sellerSecure->setRpTokenCreatedAt(null);
        $sellerSecure->setPasswordHash($this->createPasswordHash($newPassword));
        $this->sessionCleaner->clearFor((int)$seller->getId());
        $this->disableAddressValidation($seller);
        $this->sellerRepository->save($seller);

        return true;
    }

    /**
     * Create a hash for the given password
     *
     * @param string $password
     * @return string
     */
    protected function createPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * Get EAV validator
     *
     * @return Backend
     */
    private function getEavValidator()
    {
        if ($this->eavValidator === null) {
            $this->eavValidator = ObjectManager::getInstance()->get(Backend::class);
        }
        return $this->eavValidator;
    }

    /**
     * @inheritdoc
     */
    public function validate(SellerInterface $seller)
    {
        $validationResults = $this->validationResultsDataFactory->create();

        $oldAddresses = $seller->getAddresses();
        $sellerModel = $this->sellerFactory->create()->updateData(
            $seller->setAddresses([])
        );
        $seller->setAddresses($oldAddresses);

        $result = $this->getEavValidator()->isValid($sellerModel);
        if ($result === false && is_array($this->getEavValidator()->getMessages())) {
            return $validationResults->setIsValid(false)->setMessages(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                call_user_func_array(
                    'array_merge',
                    $this->getEavValidator()->getMessages()
                )
            );
        }
        return $validationResults->setIsValid(true)->setMessages([]);
    }

    /**
     * @inheritdoc
     */
    public function isEmailAvailable($sellerEmail, $websiteId = null)
    {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->sellerRepository->get($sellerEmail, $websiteId);
            return false;
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }

    /**
     * @inheritDoc
     */
    public function isSellerInStore($sellerWebsiteId, $storeId)
    {
        $ids = [];
        if ((bool)$this->configShare->isWebsiteScope()) {
            $ids = $this->storeManager->getWebsite($sellerWebsiteId)->getStoreIds();
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $ids[] = $store->getId();
            }
        }

        return in_array($storeId, $ids);
    }

    /**
     * Validate seller store id by seller website id.
     *
     * @param SellerInterface $seller
     * @return bool
     * @throws LocalizedException
     */
    public function validateSellerStoreIdByWebsiteId(SellerInterface $seller)
    {
        if (!$this->isSellerInStore($seller->getWebsiteId(), $seller->getStoreId())) {
            throw new LocalizedException(__('The store view is not in the associated website.'));
        }

        return true;
    }

    /**
     * Validate the Reset Password Token for a seller.
     *
     * @param int $sellerId
     * @param string $resetPasswordLinkToken
     *
     * @return bool
     * @throws ExpiredException If token is expired
     * @throws InputException If token or seller id is invalid
     * @throws InputMismatchException If token is mismatched
     * @throws LocalizedException
     * @throws NoSuchEntityException If seller doesn't exist
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function validateResetPasswordToken($sellerId, $resetPasswordLinkToken)
    {
        if ($sellerId !== null && $sellerId <= 0) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $sellerId, 'fieldName' => 'sellerId']
                )
            );
        }

        if ($sellerId === null) {
            //Looking for the seller.
            $sellerId = $this->getByToken
                ->execute($resetPasswordLinkToken)
                ->getId();
        }
        if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
            $params = ['fieldName' => 'resetPasswordLinkToken'];
            throw new InputException(__('"%fieldName" is required. Enter and try again.', $params));
        }
        $sellerSecureData = $this->sellerRegistry->retrieveSecureData($sellerId);
        $rpToken = $sellerSecureData->getRpToken();
        $rpTokenCreatedAt = $sellerSecureData->getRpTokenCreatedAt();
        if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
            throw new InputMismatchException(__('The password token is mismatched. Reset and try again.'));
        } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
            throw new ExpiredException(__('The password token is expired. Reset and try again.'));
        }
        return true;
    }

    /**
     * Check if seller can be deleted.
     *
     * @param int $sellerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws LocalizedException
     */
    public function isReadonly($sellerId)
    {
        $seller = $this->sellerRegistry->retrieveSecureData($sellerId);
        return !$seller->getDeleteable();
    }

    /**
     * Send email with new account related information
     *
     * @param SellerInterface $seller
     * @param string $type
     * @param string $backUrl
     * @param string $storeId
     * @param string $sendemailStoreId
     * @return $this
     * @throws LocalizedException
     * @deprecated 100.1.0
     */
    protected function sendNewAccountEmail(
        $seller,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = '0',
        $sendemailStoreId = null
    ) {
        $types = $this->getTemplateTypes();

        if (!isset($types[$type])) {
            throw new LocalizedException(
                __('The transactional account email type is incorrect. Verify and try again.')
            );
        }

        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($seller, $sendemailStoreId);
        }

        $store = $this->storeManager->getStore($seller->getStoreId());

        $sellerEmailData = $this->getFullSellerObject($seller);

        $this->sendEmailTemplate(
            $seller,
            $types[$type],
            self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            ['seller' => $sellerEmailData, 'back_url' => $backUrl, 'store' => $store],
            $storeId
        );

        return $this;
    }

    /**
     * Send email to seller when his password is reset
     *
     * @param SellerInterface $seller
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     */
    protected function sendPasswordResetNotificationEmail($seller)
    {
        return $this->sendPasswordResetConfirmationEmail($seller);
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param SellerInterface $seller
     * @param int|string|null $defaultStoreId
     * @return int
     * @deprecated 100.1.0
     * @throws LocalizedException
     */
    protected function getWebsiteStoreId($seller, $defaultStoreId = null)
    {
        if ($seller->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($seller->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Get template types
     *
     * @return array
     * @deprecated 100.1.0
     */
    protected function getTemplateTypes()
    {
        /**
         * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
         *                                                  and password is not set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
         *                                                  and password is set
         * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
         */
        $types = [
            self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
        ];
        return $types;
    }

    /**
     * Send corresponding email template
     *
     * @param SellerInterface $seller
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return $this
     * @throws MailException
     * @deprecated 100.1.0
     */
    protected function sendEmailTemplate(
        $seller,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($email === null) {
            $email = $seller->getEmail();
        }

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom(
                $this->scopeConfig->getValue(
                    $sender,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )
            ->addTo($email, $this->sellerViewHelper->getSellerName($seller))
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }

    /**
     * Check if accounts confirmation is required in config
     *
     * @param SellerInterface $seller
     * @return bool
     * @deprecated 101.0.4
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function isConfirmationRequired($seller)
    {
        return $this->accountConfirmation->isConfirmationRequired(
            $seller->getWebsiteId(),
            $seller->getId(),
            $seller->getEmail()
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address
     *
     * @param SellerInterface $seller
     * @return bool
     * @deprecated 101.0.4
     * @see AccountConfirmation::isConfirmationRequired
     */
    protected function canSkipConfirmation($seller)
    {
        if (!$seller->getId()) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($seller->getEmail());
    }

    /**
     * Check if rpToken is expired
     *
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     */
    public function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->sellerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
        $tokenTimestamp = $this->dateTimeFactory->create($rpTokenCreatedAt)->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
        if ($hourDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * Change reset password link token
     *
     * Stores new reset password link token
     *
     * @param SellerInterface $seller
     * @param string $passwordLinkToken
     * @return bool
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function changeResetPasswordLinkToken($seller, $passwordLinkToken)
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
            $sellerSecure = $this->sellerRegistry->retrieveSecureData($seller->getId());
            $sellerSecure->setRpToken($passwordLinkToken);
            $sellerSecure->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $this->setIgnoreValidationFlag($seller);
            $this->sellerRepository->save($seller);
        }
        return true;
    }

    /**
     * Send email with new seller password
     *
     * @param SellerInterface $seller
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     */
    public function sendPasswordReminderEmail($seller)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($seller);
        }

        $sellerEmailData = $this->getFullSellerObject($seller);

        $this->sendEmailTemplate(
            $seller,
            self::XML_PATH_REMIND_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['seller' => $sellerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param SellerInterface $seller
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     */
    public function sendPasswordResetConfirmationEmail($seller)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($seller);
        }

        $sellerEmailData = $this->getFullSellerObject($seller);

        $this->sendEmailTemplate(
            $seller,
            self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['seller' => $sellerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );

        return $this;
    }

    /**
     * Get address by id
     *
     * @param SellerInterface $seller
     * @param int $addressId
     * @return AddressInterface|null
     */
    protected function getAddressById(SellerInterface $seller, $addressId)
    {
        foreach ($seller->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    /**
     * Create an object with data merged from Seller and SellerSecure
     *
     * @param SellerInterface $seller
     * @return Data\SellerSecure
     * @throws NoSuchEntityException
     * @deprecated 100.1.0
     */
    protected function getFullSellerObject($seller)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedSellerData = $this->sellerRegistry->retrieveSecureData($seller->getId());
        $sellerData = $this->dataProcessor->buildOutputDataArray(
            $seller,
            \Magento\Seller\Api\Data\SellerInterface::class
        );
        $mergedSellerData->addData($sellerData);
        $mergedSellerData->setData('name', $this->sellerViewHelper->getSellerName($seller));
        return $mergedSellerData;
    }

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password)
    {
        return $this->encryptor->getHash($password, true);
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
     * Set ignore_validation_flag for reset password flow to skip unnecessary address and seller validation
     *
     * @param Seller $seller
     * @return void
     */
    private function setIgnoreValidationFlag($seller)
    {
        $seller->setData('ignore_validation_flag', true);
    }

    /**
     * Check is address allowed for store
     *
     * @param AddressInterface $address
     * @param int|null $storeId
     * @return bool
     */
    private function isAddressAllowedForWebsite(AddressInterface $address, $storeId): bool
    {
        $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);

        return in_array($address->getCountryId(), $allowedCountries);
    }
}
