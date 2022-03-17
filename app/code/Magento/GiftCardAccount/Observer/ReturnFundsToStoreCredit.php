<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftCardAccount\Observer;

use Magento\Framework\Event\ObserverInterface;

class ReturnFundsToStoreCredit implements ObserverInterface
{
    /**
     * Gift card account data
     *
     * @var \Magento\GiftCardAccount\Helper\Data
     */
    protected $giftCAHelper = null;

    /**
     * Customer balance balance
     *
     * @var \Magento\CustomerBalance\Model\Balance
     */
    protected $customerBalance = null;

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager = null;

    /**
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Magento\CustomerBalance\Model\Balance $customerBalance
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Magento\CustomerBalance\Model\Balance $customerBalance,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->giftCAHelper = $giftCAHelper;
        $this->customerBalance = $customerBalance;
        $this->storeManager = $storeManager;
    }

    /**
     * send event to sqs
     * @param mixed $DataArray
     * @return void
     */
    public function sendEventToSqs($DataArray){
        $HelperObject = \Magento\Framework\App\ObjectManager::getInstance();
        $Helper = $HelperObject->create('Smallworld\Pwa\SqsHelper\Helper');
        $Helper->EventToSqs($DataArray);
        // $Helper->OrderSMSEventSQS($dataArray);
        // $Helper->OrderEmailEventSQS($dataArray);
    }
    /**
     * refundFundsEvent
     * @param int $customerId , $orderTotal, $customerBalance,$customerPhone,$orderId
     * @param string $customerName,$customerEmail
     * @return void
     */
    public function refundFundsEvent($customerId,$customerName,$customerPhone,$customerEmail,$orderId,$orderTotal,$customerBalance){
        $DataArray = array("event_name" => "refund_funds_order_cancel",
                            "customer_id"=>$customerId,
                            "customer_name"=>$customerName,
                            "customer_phone"=>"+91".$customerPhone,
                            "customer_email"=>$customerEmail,
                            "order_id"=>$orderId,
                            "order_total"=>number_format($orderTotal),
                            "cashback_amount"=>number_format($customerBalance));
        $this->sendEventToSqs($DataArray);    
    }
    /**
     * Return funds to store credit
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $cards = $this->giftCAHelper->getCards($order);
        if (is_array($cards)) {
            $balance = 0;
            foreach ($cards as $card) {
                $balance += $card[\Magento\GiftCardAccount\Model\Giftcardaccount::BASE_AMOUNT];
            }
            if ($balance > 0) {
                $this->customerBalance->setCustomerId(
                    $order->getCustomerId()
                )->setWebsiteId(
                    $this->storeManager->getStore($order->getStoreId())->getWebsiteId()
                )->setAmountDelta(
                    $balance
                )->setHistoryAction(
                    \Magento\CustomerBalance\Model\Balance\History::ACTION_REVERTED
                )->setOrder(
                    $order
                )->save();
            }
        }$this->refundFundsEvent($order->getCustomerId(), $order->getShippingAddress()->getFirstname(),$order->getShippingAddress()->getTelephone(),$order->getBillingAddress()->getEmail(),$order->getIncrementId(),$order->getGrandTotal(),$balance );
        return $this;
    }
}
