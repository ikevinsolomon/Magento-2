<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reward\Observer;

use Magento\Framework\Event\ObserverInterface;
use Enqueue\Sqs\SqsConnectionFactory;

class ReturnRewardPoints implements ObserverInterface
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
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reward\Model\RewardFactory $rewardFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reward\Model\RewardFactory $rewardFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_rewardFactory = $rewardFactory;
    }

    /**
     * send event to sqs
     * @param mixed $DataArray
     * @return void
     */
    public function sendEventToSqs($DataArray)
    {
        $HelperObject = \Magento\Framework\App\ObjectManager::getInstance();
        $Helper = $HelperObject->create('Smallworld\Pwa\SqsHelper\Helper');
        $Helper->EventToSqs($DataArray);
        // $Helper->OrderSMSEventSQS($dataArray);
        // $Helper->OrderEmailEventSQS($dataArray);
    }
    /**
     * revertRewardEvent
     *
     * @param int $customerId , $orderTotal, $revetReward,$customerPhone
     * @param string $customerName,$customerEmail
     * @return void
     */
    public function revertRewardEvent($customerId,$customerName,$customerPhone,$customerEmail,$orderId,$orderTotal,$revetReward)
    {
        $DataArray = array("event_name"=>"return_reward_points_order_cancel",
                            "customer_id"=>$customerId,
                            "customer_name"=>$customerName,
                            "customer_phone"=>"+91".$customerPhone,
                            "customer_email"=>$customerEmail,
                            "order_id"=>$orderId,
                            "order_total"=>number_format($orderTotal),
                            "Revert Reward "=>number_format($revetReward));
        $this->sendEventToSqs($DataArray);
    }
    /**
     * Return reward points
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getRewardPointsBalance() > 0) {
            $this->_rewardFactory->create()->setCustomerId(
                $order->getCustomerId()
            )->setWebsiteId(
                $this->_storeManager->getStore($order->getStoreId())->getWebsiteId()
            )->setAction(
                \Magento\Reward\Model\Reward::REWARD_ACTION_REVERT
            )->setPointsDelta(
                $order->getRewardPointsBalance()
            )->setActionEntity(
                $order
            )->updateRewardPoints();
        }
        $this->revertRewardEvent($order->getCustomerId(),$order->getShippingAddress()->getFirstname(),$order->getShippingAddress()->getTelephone(),$order->getBillingAddress()->getEmail(),$order->getIncrementId(),$order->getGrandTotal(),$order->getRewardPointsBalance());
        return $this;
    }
}
