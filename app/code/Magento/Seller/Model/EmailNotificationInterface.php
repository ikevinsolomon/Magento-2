<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 * @since 100.1.0
 */
interface EmailNotificationInterface
{
    /**
     * Constants for the type of new account email to be sent
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';

    /**
     * Send notification to seller when email and/or password changed
     *
     * @param SellerInterface $savedSeller
     * @param string $origSellerEmail
     * @param bool $isPasswordChanged
     * @return void
     * @since 100.1.0
     */
    public function credentialsChanged(
        SellerInterface $savedSeller,
        $origSellerEmail,
        $isPasswordChanged = false
    );

    /**
     * Send email with new seller password
     *
     * @param SellerInterface $seller
     * @return void
     * @since 100.1.0
     */
    public function passwordReminder(SellerInterface $seller);

    /**
     * Send email with reset password confirmation link
     *
     * @param SellerInterface $seller
     * @return void
     * @since 100.1.0
     */
    public function passwordResetConfirmation(SellerInterface $seller);

    /**
     * Send email with new account related information
     *
     * @param SellerInterface $seller
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     * @since 100.1.0
     */
    public function newAccount(
        SellerInterface $seller,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    );
}
