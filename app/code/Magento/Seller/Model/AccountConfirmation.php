<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;

/**
 * Class AccountConfirmation. Checks if email confirmation required for seller.
 */
class AccountConfirmation
{
    /**
     * Configuration path for email confirmation.
     */
    const XML_PATH_IS_CONFIRM = 'seller/create_account/confirm';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * AccountConfirmation constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
    }

    /**
     * Check if accounts confirmation is required.
     *
     * @param int|null $websiteId
     * @param int|null $sellerId
     * @param string $sellerEmail
     * @return bool
     */
    public function isConfirmationRequired($websiteId, $sellerId, $sellerEmail): bool
    {
        if ($this->canSkipConfirmation($sellerId, $sellerEmail)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * Check whether confirmation may be skipped when registering using certain email address.
     *
     * @param int|null $sellerId
     * @param string $sellerEmail
     * @return bool
     */
    private function canSkipConfirmation($sellerId, $sellerEmail): bool
    {
        if (!$sellerId) {
            return false;
        }

        /* If an email was used to start the registration process and it is the same email as the one
           used to register, then this can skip confirmation.
           */
        $skipConfirmationIfEmail = $this->registry->registry("skip_confirmation_if_email");
        if (!$skipConfirmationIfEmail) {
            return false;
        }

        return strtolower($skipConfirmationIfEmail) === strtolower($sellerEmail);
    }
}
