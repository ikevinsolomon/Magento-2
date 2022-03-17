<?php

namespace Magento\CustomerBalance\Api\Data;

/**
 * @api
 */
interface BalanceInterface
{

	/**
     * Get balance
     *
     * @return float
     */
	 public function getBalance();
	 /**
     * Set balance
     *
     * @return float $balance
     */
	 public function setBalance($balance);
	
	/**
     * @return array
     */
    public function getBalanceHistory();
	/**
     * @param array $history
     * @return $this
     */
    public function setBalanceHistory(array $history);
    

}