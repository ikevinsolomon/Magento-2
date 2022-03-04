<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Seller\Helper\View as SellerViewHelper;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Seller\Model\Data\SellerSecure;

/**
 * Seller email notification
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailNotification implements EmailNotificationInterface
{
    /**#@+
     * Configuration paths for email templates and identities
     */
    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'seller/password/forgot_email_identity';

    const XML_PATH_RESET_PASSWORD_TEMPLATE = 'seller/password/reset_password_template';

    const XML_PATH_CHANGE_EMAIL_TEMPLATE = 'seller/account_information/change_email_template';

    const XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE =
        'seller/account_information/change_email_and_password_template';

    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'seller/password/forgot_email_template';

    const XML_PATH_REMIND_EMAIL_TEMPLATE = 'seller/password/remind_email_template';

    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'seller/create_account/email_identity';

    const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'seller/create_account/email_template';

    const XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE = 'seller/create_account/email_no_password_template';

    const XML_PATH_CONFIRM_EMAIL_TEMPLATE = 'seller/create_account/email_confirmation_template';

    const XML_PATH_CONFIRMED_EMAIL_TEMPLATE = 'seller/create_account/email_confirmed_template';

    /**
     * self::NEW_ACCOUNT_EMAIL_REGISTERED               welcome email, when confirmation is disabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD   welcome email, when confirmation is disabled
     *                                                  and password is not set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMED                welcome email, when confirmation is enabled
     *                                                  and password is set
     * self::NEW_ACCOUNT_EMAIL_CONFIRMATION             email with confirmation link
     */
    const TEMPLATE_TYPES = [
        self::NEW_ACCOUNT_EMAIL_REGISTERED => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD => self::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMED => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
        self::NEW_ACCOUNT_EMAIL_CONFIRMATION => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
    ];

    /**#@-*/

    /**#@-*/
    private $sellerRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var SellerViewHelper
     */
    protected $sellerViewHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @param SellerRegistry $sellerRegistry
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param SellerViewHelper $sellerViewHelper
     * @param DataObjectProcessor $dataProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param SenderResolverInterface|null $senderResolver
     * @param Emulation|null $emulation
     */
    public function __construct(
        SellerRegistry $sellerRegistry,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        SellerViewHelper $sellerViewHelper,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        SenderResolverInterface $senderResolver = null,
        Emulation $emulation =null
    ) {
        $this->sellerRegistry = $sellerRegistry;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->sellerViewHelper = $sellerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->senderResolver = $senderResolver ?? ObjectManager::getInstance()->get(SenderResolverInterface::class);
        $this->emulation = $emulation ?? ObjectManager::getInstance()->get(Emulation::class);
    }

    /**
     * Send notification to seller when email or/and password changed
     *
     * @param SellerInterface $savedSeller
     * @param string $origSellerEmail
     * @param bool $isPasswordChanged
     * @return void
     */
    public function credentialsChanged(
        SellerInterface $savedSeller,
        $origSellerEmail,
        $isPasswordChanged = false
    ): void {
        if ($origSellerEmail != $savedSeller->getEmail()) {
            if ($isPasswordChanged) {
                $this->emailAndPasswordChanged($savedSeller, $origSellerEmail);
                $this->emailAndPasswordChanged($savedSeller, $savedSeller->getEmail());
                return;
            }

            $this->emailChanged($savedSeller, $origSellerEmail);
            $this->emailChanged($savedSeller, $savedSeller->getEmail());
            return;
        }

        if ($isPasswordChanged) {
            $this->passwordReset($savedSeller);
        }
    }

    /**
     * Send email to seller when his email and password is changed
     *
     * @param SellerInterface $seller
     * @param string $email
     * @return void
     */
    private function emailAndPasswordChanged(SellerInterface $seller, $email): void
    {
        $storeId = $seller->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($seller);
        }

        $sellerEmailData = $this->getFullSellerObject($seller);

        $this->sendEmailTemplate(
            $seller,
            self::XML_PATH_CHANGE_EMAIL_AND_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['seller' => $sellerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId,
            $email
        );
    }

    /**
     * Send email to seller when his email is changed
     *
     * @param SellerInterface $seller
     * @param string $email
     * @return void
     */
    private function emailChanged(SellerInterface $seller, $email): void
    {
        $storeId = $seller->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($seller);
        }

        $sellerEmailData = $this->getFullSellerObject($seller);

        $this->sendEmailTemplate(
            $seller,
            self::XML_PATH_CHANGE_EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['seller' => $sellerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId,
            $email
        );
    }

    /**
     * Send email to seller when his password is reset
     *
     * @param SellerInterface $seller
     * @return void
     */
    private function passwordReset(SellerInterface $seller): void
    {
        $storeId = $seller->getStoreId();
        if ($storeId === null) {
            $storeId = $this->getWebsiteStoreId($seller);
        }

        $sellerEmailData = $this->getFullSellerObject($seller);

        $this->sendEmailTemplate(
            $seller,
            self::XML_PATH_RESET_PASSWORD_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['seller' => $sellerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
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
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmailTemplate(
        $seller,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ): void {
        $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
        if ($email === null) {
            $email = $seller->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId),
            $storeId
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->sellerViewHelper->getSellerName($seller))
            ->getTransport();

        $this->emulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND);
        $transport->sendMessage();
        $this->emulation->stopEnvironmentEmulation();
    }

    /**
     * Create an object with data merged from Seller and SellerSecure
     *
     * @param SellerInterface $seller
     * @return SellerSecure
     */
    private function getFullSellerObject($seller): SellerSecure
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedSellerData = $this->sellerRegistry->retrieveSecureData($seller->getId());
        $sellerData = $this->dataProcessor
            ->buildOutputDataArray($seller, SellerInterface::class);
        $mergedSellerData->addData($sellerData);
        $mergedSellerData->setData('name', $this->sellerViewHelper->getSellerName($seller));
        return $mergedSellerData;
    }

    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param SellerInterface $seller
     * @param int|string|null $defaultStoreId
     * @return int
     */
    private function getWebsiteStoreId($seller, $defaultStoreId = null): int
    {
        if ($seller->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($seller->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Send email with new seller password
     *
     * @param SellerInterface $seller
     * @return void
     */
    public function passwordReminder(SellerInterface $seller): void
    {
        $storeId = $seller->getStoreId();
        if ($storeId === null) {
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
    }

    /**
     * Send email with reset password confirmation link
     *
     * @param SellerInterface $seller
     * @return void
     */
    public function passwordResetConfirmation(SellerInterface $seller): void
    {
        $storeId = $seller->getStoreId();
        if ($storeId === null) {
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
    }

    /**
     * Send email with new account related information
     *
     * @param SellerInterface $seller
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    public function newAccount(
        SellerInterface $seller,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ): void {
        $types = self::TEMPLATE_TYPES;

        if (!isset($types[$type])) {
            throw new LocalizedException(
                __('The transactional account email type is incorrect. Verify and try again.')
            );
        }

        if ($storeId === null) {
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
    }
}
