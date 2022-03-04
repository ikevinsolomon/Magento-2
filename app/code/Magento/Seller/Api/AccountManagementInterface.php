<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api;

use Magento\Framework\Exception\InputException;

/**
 * Interface for managing sellers accounts.
 * @api
 * @since 100.0.2
 */
interface AccountManagementInterface
{
    /**#@+
     * Constant for confirmation status
     */
    const ACCOUNT_CONFIRMED = 'account_confirmed';
    const ACCOUNT_CONFIRMATION_REQUIRED = 'account_confirmation_required';
    const ACCOUNT_CONFIRMATION_NOT_REQUIRED = 'account_confirmation_not_required';
    const MAX_PASSWORD_LENGTH = 256;
    /**#@-*/

    /**
     * Create seller account. Perform necessary business operations like sending email.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @param string $password
     * @param string $redirectUrl
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccount(
        \Magento\Seller\Api\Data\SellerInterface $seller,
        $password = null,
        $redirectUrl = ''
    );

    /**
     * Create seller account using provided hashed password. Should not be exposed as a webapi.
     *
     * @api
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @param string $hash Password hash that we can save directly
     * @param string $redirectUrl URL fed to welcome email templates. Can be used by templates to, for example, direct
     *                            the seller to a product they were looking at after pressing confirmation link.
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccountWithPasswordHash(
        \Magento\Seller\Api\Data\SellerInterface $seller,
        $hash,
        $redirectUrl = ''
    );

    /**
     * Validate seller data.
     *
     * @param \Magento\Seller\Api\Data\SellerInterface $seller
     * @return \Magento\Seller\Api\Data\ValidationResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate(\Magento\Seller\Api\Data\SellerInterface $seller);

    /**
     * Check if seller can be deleted.
     *
     * @api
     * @param int $sellerId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If group is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isReadonly($sellerId);

    /**
     * Activate a seller account using a key that was sent in a confirmation email.
     *
     * @param string $email
     * @param string $confirmationKey
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activate($email, $confirmationKey);

    /**
     * Activate a seller account using a key that was sent in a confirmation email.
     *
     * @api
     * @param int $sellerId
     * @param string $confirmationKey
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function activateById($sellerId, $confirmationKey);

    /**
     * Authenticate a seller by username and password
     *
     * @param string $email
     * @param string $password
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate($email, $password);

    /**
     * Change seller password.
     *
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePassword($email, $currentPassword, $newPassword);

    /**
     * Change seller password.
     *
     * @param int $sellerId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePasswordById($sellerId, $currentPassword, $newPassword);

    /**
     * Send an email to the seller with a password reset link.
     *
     * @param string $email
     * @param string $template
     * @param int $websiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initiatePasswordReset($email, $template, $websiteId = null);

    /**
     * Reset seller password.
     *
     * @param string $email If empty value given then the seller
     * will be matched by the RP token.
     * @param string $resetToken
     * @param string $newPassword
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws InputException
     */
    public function resetPassword($email, $resetToken, $newPassword);

    /**
     * Check if password reset token is valid.
     *
     * @param int $sellerId If null is given then a seller
     * will be matched by the RP token.
     * @param string $resetPasswordLinkToken
     *
     * @return bool True if the token is valid
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or seller id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If seller doesn't exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateResetPasswordLinkToken($sellerId, $resetPasswordLinkToken);

    /**
     * Gets the account confirmation status.
     *
     * @param int $sellerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfirmationStatus($sellerId);

    /**
     * Resend confirmation email.
     *
     * @param string $email
     * @param int $websiteId
     * @param string $redirectUrl
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resendConfirmation($email, $websiteId, $redirectUrl = '');

    /**
     * Check if given email is associated with a seller account in given website.
     *
     * @param string $sellerEmail
     * @param int $websiteId If not set, will use the current websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isEmailAvailable($sellerEmail, $websiteId = null);

    /**
     * Check store availability for seller given the sellerId.
     *
     * @param int $sellerWebsiteId
     * @param int $storeId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isSellerInStore($sellerWebsiteId, $storeId);

    /**
     * Retrieve default billing address for the given sellerId.
     *
     * @param int $sellerId
     * @return \Magento\Seller\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the seller Id is invalid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultBillingAddress($sellerId);

    /**
     * Retrieve default shipping address for the given sellerId.
     *
     * @param int $sellerId
     * @return \Magento\Seller\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If the seller Id is invalid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDefaultShippingAddress($sellerId);

    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password);
}
