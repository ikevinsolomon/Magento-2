<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GiftCardAccount\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\GiftCardAccount\Api\RedeemInterface;

/**
 * @method \Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount _getResource()
 * @method \Magento\GiftCardAccount\Model\ResourceModel\Giftcardaccount getResource()
 * @method string getCode()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setCode(string $value)
 * @method int getStatus()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setStatus(int $value)
 * @method string getDateCreated()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setDateCreated(string $value)
 * @method string getDateExpires()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setDateExpires(string $value)
 * @method int getWebsiteId()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setWebsiteId(int $value)
 * @method float getBalance()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setBalance(float $value)
 * @method int getState()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setState(int $value)
 * @method int getIsRedeemable()
 * @method \Magento\GiftCardAccount\Model\Giftcardaccount setIsRedeemable(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redeem   extends \Magento\Framework\Model\AbstractExtensibleModel implements RedeemInterface
{
	
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
    
    public function redeem($giftcard_code,$customer_id){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$result=$objectManager->create(
                    'Magento\GiftCardAccount\Model\Giftcardaccount'
                )->loadByCode(
                    $giftcard_code
                )->setIsRedeemed(
                    true
                )->redeem($customer_id);
				//echo '<pre>'; print_r($result);exit;
				return true;
	}
	
   
}
