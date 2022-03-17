<?php

namespace Magento\GiftCard\Model;

//use Smallworld\Quote\Api\CustomercartInterface;

/**
 * Defines the implementaiton class of the calculator service contract.
 */
class Giftaddtocart implements \Magento\GiftCard\Api\GiftaddtocartInterface
{
    /**
     * Return the sum of the two numbers.
     *
     * @api
     * @param int $num1 Left hand operand.
     * @param int $num2 Right hand operand.
     * @return int The sum of the two values.
     */
  
	protected $quoteFactory;
	protected $quoteModel;
	protected $customerfactory;
    public function __construct(
	\Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Quote\Model\ResourceModel\Quote $quoteModel,
	\Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
	\Magento\Quote\Model\QuoteFactory $quote,
	\Magento\Customer\Model\CustomerFactory $customerFactory,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
	\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
	\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
	\Magento\Quote\Api\Data\ProductOptionInterface $productoption,
	\Magento\Quote\Model\Quote\Item\Repository $ItemRepository,
	\Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory,
	\Magento\Quote\Api\Data\CartItemInterface $cartitem,
	\Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
    \Magento\GiftCard\Model\Giftcard\OptionFactory $giftCardOptionFactory,
    \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
	)
    {
		$this->_storeManager = $storeManager;
		$this->quoteModel=$quoteModel;
		$this->quote = $quote;
		$this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;       
		$this->quoteRepository = $quoteRepository;
		$this->quoteIdMaskFactory = $quoteIdMaskFactory;
		$this->productRepository =$productRepository;
		$this->productOptionInterface = $productoption;
		$this->itemRepository = $ItemRepository;
		$this->extensionFactory = $extensionFactory;
		$this->cartItem = $cartitem;
		$this->dataObjectHelper = $dataObjectHelper;
        $this->giftCardOptionFactory = $giftCardOptionFactory;
        $this->productOptionFactory = $productOptionFactory;
    }
	
	/**
     * Gift product add to cart 
     *
     * @api
	 * @param mixed $data
     * @return $this
     */
	
	public function Giftaddtocart($data){
			$qty = $data['qty']; 
			if($data['is_guest']==1){
			$quoteIdMask = $this->quoteIdMaskFactory->create()->load($data['quote_id'], 'masked_id');
				$quoteId=$quoteIdMask->getQuoteId();
			}else{
				$quoteId = $data['quote_id'];
			}
		$product=$this->productRepository->get($data['sku']);
		$productid=$product->getId();
		$additionalOptions['giftcard_sender_name'] = ['label' => 'Sender Name','value' => ''.$data['giftcard_sender_name'].'',];
		$additionalOptions['giftcard_sender_email'] = ['label' => 'Sender Email','value' => ''.$data['giftcard_sender_email'].'',];
		$additionalOptions['giftcard_recipient_name'] = ['label' => 'Recipient Name','value' => ''.$data['giftcard_recipient_name'].'',];
		$additionalOptions['giftcard_recipient_email'] = ['label' => 'Recipient Email','value' => ''.$data['giftcard_recipient_email'].'',];
		$additionalOptions['giftcard_message'] = ['label' => 'Message','value' => ''.$data['giftcard_message'].'',];
		 // cartItem is an instance of Magento\Quote\Api\Data\CartItemInterface
		$cartItem = $this->cartItem;
		  $optionsArray = [];
            /** @var \Magento\Quote\Model\Quote\Item\Option  $option */
            foreach ($additionalOptions as $key => $option) {
                $optionsArray[$key] = $option['value'];
            }
            $giftOptionDataObject = $this->giftCardOptionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $giftOptionDataObject,
                $optionsArray,
                'Magento\GiftCard\Api\Data\GiftCardOptionInterface'
            );
            /** set gift card product option */
            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();
			$extensibleAttribute = $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();
			$extensibleAttribute->setGiftcardItemOption($giftOptionDataObject);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
			$productId = $productid; // assign product Id
			
			 // here give quote id
			$product = $this->productRepository->getById($productId); // _productRepository is an instance of \Magento\Catalog\Api\ProductRepositoryInterface
			// set product sku to cart item
			$cartItem->setSku($product->getSku());
			 
			// assign quote Id to cart item
			$cartItem->setQuoteId($quoteId);
			 
			// set product Quantity
			$cartItem->setQty($qty);

			// set product options to cart item
			$cartItem->setProductOption($productOption);
			 
			// add ptoduct to cart
			if($this->itemRepository->save($cartItem)){; // it will save cart item and return newly added Item
			return true;
			}else{
			return false;	
			}			
			}
	
		
	
	


    

}