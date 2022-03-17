<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerBalance\Observer;

/**
 * Customer balance observer
 */
class RevertStoreCreditForOrder
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CustomerBalance\Model\BalanceFactory
     */
    protected $_balanceFactory;


    /**
     * Constructor
     *
     * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_balanceFactory = $balanceFactory;
        $this->_storeManager = $storeManager;
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
        $Helper->OrderSMSEventSQS($DataArray);
        $Helper->OrderEmailEventSQS($DataArray);
    }

     /**
     * revertStoreEvent
     *
     * @param int $customerId , $orderTotal, $revetcredit,$customerPhone
     * @param string $customerName, $customerEmail
     * @return void
     */
    public function revertStoreEvent($customerId,$customerName,$customerPhone,$customerEmail,$orderId,$orderTotal,$revetcredit)
    {
        $DataArray = array("event_name"=>"revert_store_credit_amount_order_cancel",
                            "customer_id"=>$customerId,
                            "customer_name"=>$customerName,
                            "customer_phone"=>"+91".$customerPhone,
                            "customer_email"=>$customerEmail,
                            "order_id"=>$orderId,
                            "order_total"=>number_format($orderTotal),
                            "revert_store_credit_amount_order"=>number_format($revetcredit));
        $this->sendEventToSqs($DataArray);   
    }
    /**
     * Revert authorized store credit amount for order
     *
     * @param   \Magento\Sales\Model\Order $order
     * @return  $this
     */
    public function execute(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getCustomerId() || !$order->getBaseCustomerBalanceAmount()) {
            return $this;
        }
        $eligibleForCashback = true;  
        foreach ($order->getStatusHistoryCollection() as $status) {
            $comment = $status->getComment();
            if ($comment) {
                if (str_contains($comment, 'Reverted store credits')) { 
                    $eligibleForCashback = false;
                }
            }
        }
        if($eligibleForCashback) {
            $this->_balanceFactory->create()->setCustomerId(
                $order->getCustomerId()
            )->setWebsiteId(
                $this->_storeManager->getStore($order->getStoreId())->getWebsiteId()
            )->setAmountDelta(
                $order->getBaseCustomerBalanceAmount()
            )->setHistoryAction(
                \Magento\CustomerBalance\Model\Balance\History::ACTION_REVERTED
            )->setOrder(
                $order
            )->save(); 
            $order->addStatusHistoryComment(__('Reverted store credits'));
            $order->save();   
            $this->revertStoreEvent($order->getCustomerId(),$order->getShippingAddress()->getFirstname(),$order->getShippingAddress()->getTelephone(),$order->getBillingAddress()->getEmail(),$order->getIncrementId(),$order->getGrandTotal(),$order->getBaseCustomerBalanceAmount());
            return $this;
        }
    }
}
