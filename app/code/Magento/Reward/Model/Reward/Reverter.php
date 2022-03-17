<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reward\Model\Reward;

class Reverter
{
    /**
     * Reward factory
     *
     * @var \Magento\Reward\Model\RewardFactory
     */
    protected $_rewardFactory;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Reward\Model\ResourceModel\RewardFactory
     */
    protected $rewardResourceFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reward\Model\RewardFactory $rewardFactory
     * @param \Magento\Reward\Model\ResourceModel\RewardFactory $rewardResourceFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reward\Model\RewardFactory $rewardFactory,
        \Magento\Reward\Model\ResourceModel\RewardFactory $rewardResourceFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_rewardFactory = $rewardFactory;
        $this->rewardResourceFactory = $rewardResourceFactory;
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
     * revertRewardPointsEvent
     *
     * @param int $customerId , $orderTotal, $cashBack,$customerPhone,$orderId
     * @param string $customerName,$customerEmail
     * @return void
     */
    public function revertRewardPointsEvent($customerId,$customerName,$customerPhone,$customerEmail,$orderId,$orderTotal,$cashBack){
        $DataArray = array("event_name" => "quote_submit_failure_revert_reward",
                            "customer_id"=>$customerId,
                            "customer_name"=>$customerName,
                            "customer_phone"=>"+91".$customerPhone,
                            "customer_email"=>$customerEmail,
                            "order_id"=>$orderId,
                            "order_total"=>number_format($orderTotal),
                            "cashback_amount"=>number_format($cashBack));
        $this->sendEventToSqs($DataArray);
            
    }
    /**
     * revertRewardEarnedPointsEvent
     *
     * @param int $customerId , $orderTotal, $revert,$customerPhone,$orderId
     * @param string $customerName,$customerEmail
     * @return void
     */
    public function revertRewardEarnedPointsEvent($customerId,$customerName,$customerPhone,$customerEmail,$orderId,$orderTotal,$revert){
        $DataArray = array("event_name" => "quote_submit_failure_revert_reward_earned_Points",
                            "customer_id"=>$customerId,
                            "customer_name"=>$customerName,
                            "customer_phone"=>"+91".$customerPhone,
                            "customer_email"=>$customerEmail,
                            "order_id"=>$orderId,
                            "order_total"=>number_format($orderTotal),
                            "cashback_amount"=>number_format($revert));
        $this->sendEventToSqs($DataArray);    
    }

    /**
     * Revert authorized reward points amount for order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function revertRewardPointsForOrder(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getCustomerId()) {
            return $this;
        }
        $this->_rewardFactory->create()->setCustomerId(
            $order->getCustomerId()
        )->setWebsiteId(
            $this->_storeManager->getStore($order->getStoreId())->getWebsiteId()
        )->setPointsDelta(
            $order->getRewardPointsBalance()
        )->setAction(
            \Magento\Reward\Model\Reward::REWARD_ACTION_REVERT
        )->setActionEntity(
            $order
        )->updateRewardPoints();
        $this->revertRewardPointsEvent($order->getCustomerId(), $order->getShippingAddress()->getFirstname(),$order->getShippingAddress()->getTelephone(),$order->getBillingAddress()->getEmail(),$order->getIncrementId(),$order->getGrandTotal(),$order->getRewardPointsBalance());
        return $this;
    }

    /**
     * Revert sales rule earned reward points for order.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function revertEarnedRewardPointsForOrder(\Magento\Sales\Model\Order $order)
    {
        $appliedRuleIds = array_unique(explode(',', $order->getAppliedRuleIds()));
        /** @var $resource \Magento\Reward\Model\ResourceModel\Reward */
        $rewardRules = $this->rewardResourceFactory->create()->getRewardSalesrule($appliedRuleIds);
        $pointsDelta = array_sum(array_column($rewardRules, 'points_delta'));

        if ($pointsDelta && !$order->getCustomerIsGuest()) {
            $reward = $this->_rewardFactory->create();
            $reward->setCustomerId(
                $order->getCustomerId()
            )->setWebsiteId(
                $this->_storeManager->getStore($order->getStoreId())->getWebsiteId()
            )->setPointsDelta(
                -$pointsDelta
            )->setAction(
                \Magento\Reward\Model\Reward::REWARD_ACTION_REVERT
            )->setActionEntity(
                $order
            )->updateRewardPoints();
        }
        $this->revertRewardEarnedPointsEvent($order->getCustomerId(), $order->getShippingAddress()->getFirstname(),$order->getShippingAddress()->getTelephone(),$order->getBillingAddress()->getEmail(),$order->getIncrementId(),$order->getGrandTotal(),$pointsDelta);
        return $this;
    }
}
