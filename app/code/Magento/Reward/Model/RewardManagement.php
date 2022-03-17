<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reward\Model;

class RewardManagement implements \Magento\Reward\Api\RewardManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Reward helper
     *
     * @var \Magento\Reward\Helper\Data
     */
    protected $rewardData;

    /**
     * @var \Magento\Reward\Model\PaymentDataImporter
     */
    protected $importer;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Reward\Helper\Data $rewardData
     * @param PaymentDataImporter $importer
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Reward\Helper\Data $rewardData,
        \Magento\Reward\Model\PaymentDataImporter $importer
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->rewardData = $rewardData;
        $this->importer = $importer;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId)
    {
        if ($this->rewardData->isEnabledOnFront()) {
            /* @var $quote \Magento\Quote\Model\Quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $this->importer->import($quote, $quote->getPayment(), true);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            return true;
        }
        return false;
    }

     /**
     * Get orders cashback amount.
     * @param int $orderId
     * @param string $userEmailHash
     * @return string 
     * @throw LocalizedException
     */
    public function getCashBack($orderId,$userEmailHash)
    {   
        $pointsDelta = [];
        $finalResult = [];
        $OrderObject = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $OrderObject->create('\Magento\Sales\Model\Order')->load($orderId);
        $incrementId =  $order->getIncrementId();
        $userEmail = $order->getBillingAddress()->getEmail();
        if(md5($userEmail)== $userEmailHash)
        {
            $query_Object = \Magento\Framework\App\ObjectManager::getInstance();
            $temp_query = $query_Object->create('CashBack\AddCashBack\Model\CashBackAdd')
                                        ->getCollection()
                                        ->addFieldToFilter('order_id',$incrementId);
            if($temp_query->getSize()!=0){
                foreach($temp_query as $item){
                //$pointsDelta += $item->getData('cashback_amount');
                $result = array(
                    "cashback_amount" => $item->getData('cashback_amount')
                );
                array_push($finalResult,$result);
                }
            }
    
            $temp_query1 = $query_Object->create('\Magento\Sales\Model\Order')
                                        ->getCollection()
                                        ->addFieldToFilter('increment_id',$incrementId);
            if($temp_query1->getSize()!=0){
                foreach($temp_query1 as $item1){
                //$pointsDelta += $item1->getData('customer_balance_amount');
                $result = array(
                    "customer_balance_amount" => $item1->getData('customer_balance_amount')
                );
                array_push($finalResult,$result);
                }
            }
            return $finalResult;
        }
        else{
            throw new \Magento\Framework\Exception\LocalizedException(__('Consumer is not authorized'));
        }
    }
}
