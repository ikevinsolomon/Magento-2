<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerBalance\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address;

class Customerbalance extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Customer balance data
     *
     * @var \Magento\CustomerBalance\Helper\Data
     */
    protected $_customerBalanceData = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $_balanceFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\CustomerBalance\Helper\Data $customerBalanceData
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\CustomerBalance\Helper\Data $customerBalanceData,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManager;
        $this->_balanceFactory = $balanceFactory;
        $this->_customerBalanceData = $customerBalanceData;
        $this->setCode('customerbalance');
    }

    /**
     * Collect customer balance totals for specified address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return Customerbalance
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if (!$this->_customerBalanceData->isEnabled()) {
            return $this;
        }

        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == Address::TYPE_SHIPPING
            && $quote->isVirtual()
        ) {
            return $this;
        }
        $percentageUsed = intval($this->_customerBalanceData->getPercentage());
        $baseTotalUsed = $totalUsed = $baseUsed = $used = 0;
        $baseBalance = $balance = 0;
        if ($quote->getCustomer()->getId()) {
            if ($quote->getUseCustomerBalance()) {
                $store = $this->_storeManager->getStore($quote->getStoreId());
                $baseBalance = $this->_balanceFactory->create()->setCustomer(
                    $quote->getCustomer()
                )->setCustomerId(
                    $quote->getCustomer()->getId()
                )->setWebsiteId(
                    $store->getWebsiteId()
                )->loadByCustomer()->getAmount();
                $balance = $this->priceCurrency->convert($baseBalance, $quote->getStore());
            }
        }
        $quoteTotal = $quote->getBaseSubtotalWithDiscount();
        $baseBalanceAbleUsed = ($quoteTotal * $percentageUsed)/100;
        $balanceAbleUsed= ($quoteTotal * $percentageUsed)/100;

        if($baseBalanceAbleUsed > $baseBalance){
            $baseBalanceAbleUsed = $baseBalance;
        }
        if($balanceAbleUsed > $balance){
            $balanceAbleUsed = $balance;
        }
        $baseAmountLeft = $baseBalanceAbleUsed - $quote->getBaseCustomerBalAmountUsed();
        $amountLeft = $balanceAbleUsed - $quote->getCustomerBalanceAmountUsed();

        if ($baseAmountLeft >= $total->getBaseGrandTotal()) {
            $baseUsed = $total->getBaseGrandTotal();
            $used = $total->getGrandTotal();        
            $total->setBaseGrandTotal(0);
             $total->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseAmountLeft);
            $total->setGrandTotal($total->getGrandTotal() - $amountLeft);
        }

        $baseTotalUsed = $quote->getBaseCustomerBalAmountUsed() + $baseUsed;
        $totalUsed = $quote->getCustomerBalanceAmountUsed() + $used;
        if($used < 0) {
            $used = 0;
        }
        if($baseTotalUsed < 0) {
            $baseTotalUsed = 0;
        }
        if($totalUsed < 0) {
            $totalUsed = 0;
        }
        if($baseUsed < 0) {
            $baseUsed = 0;
        }
        $quote->setBaseCustomerBalAmountUsed($baseTotalUsed);
        $quote->setCustomerBalanceAmountUsed($totalUsed);

        $total->setBaseCustomerBalanceAmount($baseUsed);
        $total->setCustomerBalanceAmount($used);

        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Total $total
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($this->_customerBalanceData->isEnabled() && $total->getCustomerBalanceAmount()) {
            return [
                'code' => $this->getCode(),
                'title' => __('Mamacash'),
                'value' => -$total->getCustomerBalanceAmount()
            ];
        }

        return null;
    }
}
