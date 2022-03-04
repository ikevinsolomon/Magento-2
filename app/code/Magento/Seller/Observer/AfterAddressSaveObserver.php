<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Observer;

use Magento\Seller\Api\GroupManagementInterface;
use Magento\Seller\Helper\Address as HelperAddress;
use Magento\Seller\Model\Address;
use Magento\Seller\Model\Address\AbstractAddress;
use Magento\Seller\Model\Session as SellerSession;
use Magento\Seller\Model\Vat;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Seller Observer Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * VAT ID validation processed flag code
     */
    const VIV_PROCESSED_FLAG = 'viv_after_address_save_processed';

    /**
     * @var HelperAddress
     */
    protected $_sellerAddress;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Vat
     */
    protected $_sellerVat;

    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var SellerSession
     */
    private $sellerSession;

    /**
     * @param Vat $sellerVat
     * @param HelperAddress $sellerAddress
     * @param Registry $coreRegistry
     * @param GroupManagementInterface $groupManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     * @param AppState $appState
     * @param SellerSession $sellerSession
     */
    public function __construct(
        Vat $sellerVat,
        HelperAddress $sellerAddress,
        Registry $coreRegistry,
        GroupManagementInterface $groupManagement,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Escaper $escaper,
        AppState $appState,
        SellerSession $sellerSession
    ) {
        $this->_sellerVat = $sellerVat;
        $this->_sellerAddress = $sellerAddress;
        $this->_coreRegistry = $coreRegistry;
        $this->_groupManagement = $groupManagement;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
        $this->appState = $appState;
        $this->sellerSession = $sellerSession;
    }

    /**
     * Address after save event handler
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        /** @var $sellerAddress Address */
        $sellerAddress = $observer->getSellerAddress();
        $seller = $sellerAddress->getSeller();

        if (!$this->_sellerAddress->isVatValidationEnabled($seller->getStore())
            || $this->_coreRegistry->registry(self::VIV_PROCESSED_FLAG)
            || !$this->_canProcessAddress($sellerAddress)
            || $sellerAddress->getShouldIgnoreValidation()
        ) {
            return;
        }

        try {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, true);

            if ($sellerAddress->getVatId() == ''
                || !$this->_sellerVat->isCountryInEU($sellerAddress->getCountry())
            ) {
                $defaultGroupId = $seller->getGroupId() ? $seller->getGroupId() :
                    $this->_groupManagement->getDefaultGroup($seller->getStore())->getId();
                if (!$seller->getDisableAutoGroupChange() && $seller->getGroupId() != $defaultGroupId) {
                    $seller->setGroupId($defaultGroupId);
                    $seller->save();
                    $this->sellerSession->setSellerGroupId($defaultGroupId);
                }
            } else {
                $result = $this->_sellerVat->checkVatNumber(
                    $sellerAddress->getCountryId(),
                    $sellerAddress->getVatId()
                );

                $newGroupId = $this->_sellerVat->getSellerGroupIdBasedOnVatNumber(
                    $sellerAddress->getCountryId(),
                    $result,
                    $seller->getStore()
                );

                if (!$seller->getDisableAutoGroupChange() && $seller->getGroupId() != $newGroupId) {
                    $seller->setGroupId($newGroupId);
                    $seller->save();
                    $this->sellerSession->setSellerGroupId($newGroupId);
                }

                $sellerAddress->setVatValidationResult($result);

                if ($this->appState->getAreaCode() == Area::AREA_FRONTEND) {
                    if ($result->getIsValid()) {
                        $this->addValidMessage($sellerAddress, $result);
                    } elseif ($result->getRequestSuccess()) {
                        $this->addInvalidMessage($sellerAddress);
                    } else {
                        $this->addErrorMessage($sellerAddress);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_coreRegistry->register(self::VIV_PROCESSED_FLAG, false, true);
        }
    }

    /**
     * Check whether specified address should be processed in after_save event handler
     *
     * @param Address $address
     * @return bool
     */
    protected function _canProcessAddress($address)
    {
        if ($address->getForceProcess()) {
            return true;
        }

        if ($this->_coreRegistry->registry(BeforeAddressSaveObserver::VIV_CURRENTLY_SAVED_ADDRESS) != $address->getId()
        ) {
            return false;
        }

        $configAddressType = $this->_sellerAddress->getTaxCalculationAddressType();
        if ($configAddressType == AbstractAddress::TYPE_SHIPPING) {
            return $this->_isDefaultShipping($address);
        }

        return $this->_isDefaultBilling($address);
    }

    /**
     * Check whether specified billing address is default for its seller
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultBilling($address)
    {
        return $address->getId() && $address->getId() == $address->getSeller()->getDefaultBilling()
            || $address->getIsPrimaryBilling()
            || $address->getIsDefaultBilling();
    }

    /**
     * Check whether specified shipping address is default for its seller
     *
     * @param Address $address
     * @return bool
     */
    protected function _isDefaultShipping($address)
    {
        return $address->getId() && $address->getId() == $address->getSeller()->getDefaultShipping()
            || $address->getIsPrimaryShipping()
            || $address->getIsDefaultShipping();
    }

    /**
     * Add success message for valid VAT ID
     *
     * @param Address $sellerAddress
     * @param DataObject $validationResult
     * @return $this
     */
    protected function addValidMessage($sellerAddress, $validationResult)
    {
        $message = [
            (string)__('Your VAT ID was successfully validated.'),
        ];

        $seller = $sellerAddress->getSeller();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$seller->getDisableAutoGroupChange()
        ) {
            $sellerVatClass = $this->_sellerVat->getSellerVatClass(
                $sellerAddress->getCountryId(),
                $validationResult
            );
            $message[] = $sellerVatClass == Vat::VAT_CLASS_DOMESTIC
                ? (string)__('You will be charged tax.')
                : (string)__('You will not be charged tax.');
        }

        $this->messageManager->addSuccess(implode(' ', $message));

        return $this;
    }

    /**
     * Add error message for invalid VAT ID
     *
     * @param Address $sellerAddress
     * @return $this
     */
    protected function addInvalidMessage($sellerAddress)
    {
        $vatId = $this->escaper->escapeHtml($sellerAddress->getVatId());
        $message = [
            (string)__('The VAT ID entered (%1) is not a valid VAT ID.', $vatId),
        ];

        $seller = $sellerAddress->getSeller();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$seller->getDisableAutoGroupChange()
        ) {
            $message[] = (string)__('You will be charged tax.');
        }

        $this->messageManager->addErrorMessage(implode(' ', $message));

        return $this;
    }

    /**
     * Add error message
     *
     * @param Address $sellerAddress
     * @return $this
     */
    protected function addErrorMessage($sellerAddress)
    {
        $message = [
            (string)__('Your Tax ID cannot be validated.'),
        ];

        $seller = $sellerAddress->getSeller();
        if (!$this->scopeConfig->isSetFlag(HelperAddress::XML_PATH_VIV_DISABLE_AUTO_ASSIGN_DEFAULT)
            && !$seller->getDisableAutoGroupChange()
        ) {
            $message[] = (string)__('You will be charged tax.');
        }

        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $message[] = (string)__('If you believe this is an error, please contact us at %1', $email);

        $this->messageManager->addErrorMessage(implode(' ', $message));

        return $this;
    }
}
