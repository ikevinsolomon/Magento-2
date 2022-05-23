<?php
namespace Honasa\Quote\Model\Rewrite;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Exception\InputException;

/**
 * Class Custom
 * @package Mageplaza\HelloWorld\Model\Total\Quote
 */
class Total extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->setCode('prepaid');
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
    }

    public function getQuoteTotals($quote)
    {
            $quoteId = $quote->getId();
            $nodeUrl = $this->scopeConfig->getValue('pwa/nodeuser/url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $getTotalsByNode= $nodeUrl."/v1/carts/user/$quoteId/totals";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $getTotalsByNode);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $storeCredit = 0;
            if ($response == 200) {
                $result = JSON_DECODE($result);
        	foreach($result->totals->total_segments as $keySeg => $res)
        	{
            	if($result->totals->total_segments[$keySeg]->code =='customerbalance')
            	{
            	   $storeCredit = $res->value;
            	}
        	}

            }else{
                throw new InputException(__('Sorry! You cannot place order.'));
            }
            return $storeCredit;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $quote->getAllVisibleItems();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        // Check product is tuff or not
        $checkTuffProduct = 0;
        $totalDiscount = 0;
        $totalPrice = 0;
        foreach ($items as $item) {
            $proPrice   = $item->getPrice();
            $itemProductObj = $productRepository->get($item->getSku());
            // Check TUFF Product
            if (in_array(37, $itemProductObj->getCategoryIds())) {
                $checkTuffProduct = 1;
            }
 	              $totalDiscount   += $item->getDiscountAmount();
                  $totalPrice      += $item->getBaseRowTotalInclTax();
        }
        $method = $quote->getPayment()->getMethod();
        // checkmo is razorpay and banktransfer is paypal
        $paymentMethodConfig = $this->scopeConfig->getValue('pwa/nodeuser/online_discount_methods', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $paymentMethodCodesToApplyDiscountOn= explode(",",$paymentMethodConfig);
        // this code adds 5% discount to online payment methods
        $discount_enabled = $this->scopeConfig->getValue('pwa/nodeuser/online_discount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (
            !empty($method)
            && in_array($method, $paymentMethodCodesToApplyDiscountOn) && $checkTuffProduct == 0
         && intval($discount_enabled)==1) {

            if($quote->getCustomer()->getId() && $quote->getIsActive())
            {
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName =  $connection->getTableName('quote');
                if($quote->getEntityId()){
                    $query = 'select customer_balance_amount_used from '.$tableName.' where entity_id = '.$quote->getEntityId();
                   try {
                       $result = $connection->fetchOne($query);
                       $storeCredit = isset($result) ? $result : 0;
                   }
                   catch (\Exception $e){
                       $storeCredit = 0;
                   }
                }
                else{
                    $storeCredit = 0;
                }
                \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug('STORE_CREDIT_VALUE', ['STORE_CREDIT' => $storeCredit]);
            }else{
                $storeCredit = 0;
            }
            $address = $shippingAssignment->getShipping()->getAddress();
            $label = '5% discount for online payment';
            $TotalAmount1 = $totalPrice + $quote->getShippingAddress()->getShippingAmount();
            $TotalAmount2 = $TotalAmount1 - $totalDiscount + $storeCredit;

            $TotalAmount = (5 / 100) * $TotalAmount2; //Set 10% discount
            $discountAmount = -$TotalAmount;
            $appliedCartDiscount = 0;

            if ($total->getDiscountDescription()) {
                $appliedCartDiscount = $total->getDiscountAmount();
                $discountAmount = $total->getDiscountAmount() + $discountAmount;
                $label = $total->getDiscountDescription() . ', ' . $label;
            }

            $total->setDiscountDescription($label);
            $total->setDiscountAmount($discountAmount);
            $total->setBaseDiscountAmount($discountAmount);
            $total->setSubtotalWithDiscount($total->getSubtotal() + $discountAmount);
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $discountAmount);

            if (isset($appliedCartDiscount)) {
                $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
                $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
            } else {
                $total->addTotalAmount($this->getCode(), $discountAmount);
                $total->addBaseTotalAmount($this->getCode(), $discountAmount);
            }
        }

        return $this;

    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0) {
            $description = $total->getDiscountDescription();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $amount,
            ];
        }
        return $result;
    }

}
