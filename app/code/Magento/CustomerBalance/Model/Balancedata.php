<?php

namespace Magento\CustomerBalance\Model;

//use \Magento\CustomerBalance\Api\Data\BalanceInterface;

class Balancedata extends \Magento\Framework\Model\AbstractModel implements
    \Magento\CustomerBalance\Api\Data\BalanceInterface 
{
    const KEY_BALANCE = 'balance';
	

	private $rowset = [];
     public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

	
	/**
     * @return float
     */
	
	public function getBalance()
    {
        return $this->_getData(self::KEY_BALANCE);
    }
	 /**
     * Set name
     *
     * @param float $balance
     * @return $this
     */
	public function setBalance($balance)
    {
        return $this->setData(self::KEY_BALANCE, $balance);
    }
	
    public function getBalanceHistory()
    {
        return $this->banace_history;
    }
	 /**
     * @param array $banace_history
     * @return $this
     */
    public function setBalanceHistory(array $banace_history)
    {
        $this->banace_history = $banace_history;
        return $this;
    }
    


}