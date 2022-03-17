<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerBalance\Model;

use \Magento\CustomerBalance\Api\BalanceManagementInterface;

class BalanceManagement extends \Magento\Framework\Model\AbstractModel implements BalanceManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;
	protected $dataFactory;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
	 * @param \Magento\CustomerBalance\Model\BalanceFactory $balanceFactory
	 * @param \Magento\CustomerBalance\Model\Balance\HistoryFactory $historyFactory
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
		\Magento\CustomerBalance\Model\BalanceFactory $balanceFactory,
		\Magento\CustomerBalance\Model\Balance\HistoryFactory $historyFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\CustomerBalance\Api\Data\BalanceInterfaceFactory $dataFactory
    ) {
        $this->cartRepository = $cartRepository;
		$this->_balanceFactory = $balanceFactory;
		$this->_historyFactory = $historyFactory;
		$this->_storeManager = $storeManager;
		$this->dataFactory = $dataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($cartId)
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $this->cartRepository->get($cartId);
        $quote->setUseCustomerBalance(true);
        $quote->collectTotals();
        $quote->save();
        return true;
    }
	/**
     * Apply store credit
     *
     * @param int $customer_id
     * @return \Magento\CustomerBalance\Api\Data\BalanceInterface
     */
    public function storecredit($customer_id)
    {
        //echo $customer_id;exit;
		$model = $this->_balanceFactory->create()->setCustomerId($customer_id)->loadByCustomer();
		//return $model->getAmount();
		$collection = $this->_historyFactory->create()->getCollection()->addFieldToFilter(
            'customer_id',
            $customer_id
        )->addFieldToFilter(
            'website_id',
            $this->_storeManager->getStore()->getWebsiteId()
        )->addOrder(
            'updated_at',
            'DESC'
        )->addOrder(
            'history_id',
            'DESC'
        );
		$historydata=array();
		
		if(count($collection)>0){
		foreach($collection as $history){
			$onerecord=array();
			$onerecord['action']=$this->getLeabel($history->getAction());
			$onerecord['balance_change']=$history->getBalanceDelta();
			$onerecord['balance_amount']=$history->getBalanceAmount();
			$onerecord['updated_at']=$history->getUpdatedAt();
			$historydata[]=$onerecord;
		}
		
		}
		
		$object = $this->dataFactory->create();
		$object->setBalance($model->getAmount());
		$object->setBalanceHistory($historydata);
		return $object;
		//echo '<pre>'; print_r($historydata);exit;
    }
	
	public function getLeabel($action){
		if($action==1){
			return 'Updated';
		}else if($action==2){
			return 'Created';
		}else if($action==3){
			return 'Used';
		}else if($action==4){
			return 'Refunded';
		}else if($action==5){
			return 'Reverted';
		}else{
			return '';
		}
	}
	
	
}
