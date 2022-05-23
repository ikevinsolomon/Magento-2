<?php

namespace Honasa\Quote\Model;

use Honasa\Quote\Api\CustomerCartInterface;
use \Magento\Quote\Model\Quote\Item;
use \Magento\Store\Model\StoreManagerInterface;
use \Honasa\Quote\Api\Data\CartdataInterfaceFactory;
use \Honasa\Quote\Model\CouponManagement;
use \Magento\Quote\Model\QuoteFactory;
use \Magento\Quote\Model\ResourceModel\Quote;
use \Magento\Quote\Model\QuoteIdMaskFactory;
use \Magento\Customer\Model\CustomerFactory;
use \Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Quote\Model\QuoteRepository\SaveHandler;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Honasa\Quote\Api\Data\DuplicatecartInterfaceFactory;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Quote\Api\CartManagementInterface;

/**
 * Defines the implementaiton class of the calculator service contract.
 */
class CartItem implements CustomerCartInterface
{
    public function __construct(
        StoreManagerInterface $storeManager,
        CartdataInterfaceFactory $dataFactory,
        QuoteFactory $quoteFactory,
        Quote $quoteModel,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CustomerFactory $customerFactory,
        CartRepositoryInterface $quoteRepository,
        SaveHandler $saveHandler,
        CustomerRepositoryInterface $customerRepository,
        DuplicatecartInterfaceFactory $duplicatedata,
        ProductRepository $productRepository,
        CartManagementInterface $quoteManagement,
        CouponManagement $couponManagement,
        Item $item
    ) {
        $this->_storeManager = $storeManager;
        $this->dataFactory = $dataFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteModel = $quoteModel;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->saveHandler = $saveHandler;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->duplicatedata = $duplicatedata;
        $this->quoteManagement = $quoteManagement;
        $this->couponManagement = $couponManagement;
        $this->item = $item;
    }

    private function getConfigurableOptions($options)
    {
        $configurableOptions = array();
        try {
            // traverse options array 
            if (array_key_exists('attributes_info', $options)) {
                $customOptions = $options['attributes_info'];
                foreach ($customOptions as $customOption) {
                    $option = array();
                    $option['label'] = $customOption['label'];
                    $option['value'] = $customOption['value'];
                    $option['optionId'] = $customOption['option_id'];
                    $option['optionValue'] = $customOption['option_value'];
                    $configurableOptions[] = $option;
                }
            }
        }
        catch(Exception $e){
            throw $e; 
        }

        return $configurableOptions;

    }

    private function getCartProduct($item)
    {
        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        
        $configurableOptions = $this->getConfigurableOptions($options);
        
        $product = $this->productRepository->getById($item->getProductId());
        // get child product in case of configurable product
        $isConfigurable = $product->getTypeId() === 'configurable';
        $childProduct = $product;
        if ($isConfigurable) {
            $childProduct = $this->productRepository->get($item->getSku());
        }
        $store = $this->_storeManager->getStore();
        if (!empty($product->getImage())) {
            $imageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $childProduct->getImage();
        } else {
            $imageUrl = "";
        }

        $slug = $childProduct->getUrlKey();

        $cartItem = array();
        $cartItem['item_id'] = (int) $item->getItemId();
        $cartItem['id'] = (int) $childProduct->getId();
        $cartItem['product_id'] = (int) $childProduct->getId();
        $cartItem['old_productid'] = (int) $product->getOldProductid();
        $cartItem['categories'] = $product->getCategoryIds();
        $cartItem['sku'] = $item->getSku();
        $cartItem['qty'] = $item->getQty();
        $cartItem['name'] = $childProduct->getName();
        $cartItem['price'] = number_format($item->getPriceInclTax(), 2, '.', ',');
        $cartItem['price_excl_tax'] = (float) $item->getPrice();
        $cartItem['slug'] = $slug;
        $cartItem['image'] = $imageUrl;
        $cartItem['product_type'] = $item->getProductType();
        $cartitem['discount_amount'] = $item->getDiscountAmount();
        if (array_key_exists('attributes_info', $options)) {
            $cartItem['configurableOption'] = $configurableOptions;
        }
        return $cartItem;
    }

    private function setCartItemData($quote, $quoteId)
    {
        try{
            // set quote items data
            $items = $quote->getAllVisibleItems();
            $cartItems = array();

            foreach($items as $item){
                $cartItem = $this->getCartProduct($item);
                $cartItem['quote_id'] = $quoteId;
                $cartItems[] = $cartItem;
            }

            $object = $this->dataFactory->create();
            $object->setId((int) $quote->getId());
            $object->setCreatedAt($quote->getCreatedAt());
            $object->setUpdatedAt($quote->getUpdatedAt());
            $object->setIsActive($quote->getIsActive());
            $object->setItems($cartItems);
            
            return $object;
        }
        catch(Exception $e){
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Something went wrong while getting cart details.')
            );
        }
    }

    private function addItemsToCart($items, $quote, $cartId)
    {
        $cartItems = array();
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $product = clone $this->productRepository->getById($productId);
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

            $info = $options['info_buyRequest'];
            $info['qty'] = $item->getQty();
            $cartProductDetail = new \Magento\Framework\DataObject();
            $cartProductDetail->setData($info);
            $quote->addProduct($product, $cartProductDetail);
            // get product details in cart for response
            $cartItem = $this->getCartProduct($item);
            $cartitem['quote_id'] = $cartId;
            $cartItems[] = $cartitem;
        }
        return $cartItems;
    }

    /**
     * Register Device.
     *
     * @api
     * @param int $customer_id
     * @return $this
    */
    public function createQuote($customer_id)
    {
        $quotes = $this->quoteFactory->create()->getCollection()->addFieldToFilter('customer_id', $customer_id);
        if (count($quotes) > 0) {
            foreach ($quotes as $onequote) {
                $onequote->delete();
            }
        }
        $store = $this->_storeManager->getStore();
        $quote = $this->quoteFactory->create(); // Create Quote Object
        $quote->setStore($store); // Set Store
        $customer = $this->customerRepository->getById($customer_id);
        $quote->assignCustomer($customer);
        $quote->save();
        return $quote->getId();
    }

    /**
     * Duplicate quote for customer
     *
     * @api
     * @param int $customer_id
     * @param int $quote_id
     * @return \Honasa\Quote\Api\Data\DuplicatecartInterface
    */
    public function duplicateQuote($quote_id, $customer_id)
    {
        $oldQuote = $this->quoteFactory->create()->load($quote_id);
        $store = $this->_storeManager->getStore();
        $items = $oldQuote->getAllVisibleItems();
        $customer = $this->customerRepository->getById($customer_id);
        $newQuote = $this->quoteFactory->create(); // Create Quote Object
        $newQuote->setStore($store); // Set Store
        $newQuote->assignCustomer($customer);
        $newQuote->save();
        // Set coupon code in present in old quote
        if($oldQuote->getCouponCode()){
            $newQuote->setCouponCode($oldQuote->getCouponCode());
        }
        // Add products to new quote
        $cartItems = $this->addItemsToCart($items, $newQuote, $newQuote->getEntityId());
        $newQuote->collectTotals();
        $newQuote->save();
        $object = $this->duplicatedata->create();
        $object->setId((int) $newQuote->getEntityId());
        $object->setItems($cartItems);

        return $object;
    }

    /**
     * Duplicate quote for guest
     *
     * @api
     * @param string $quote_id
     * @return \Honasa\Quote\Api\Data\DuplicatecartInterface
    */
    public function duplicateQuoteGuest($quote_id)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote_id, 'masked_id');
        $oldQuote = $this->quoteFactory->create()->load($quoteIdMask->getQuoteId());
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $cartItems = array();
        $cartId = $this->quoteManagement->createEmptyCart();
        $quoteIdMask->setQuoteId($cartId)->save();
        $newQuote = $this->quoteFactory->create()->load($cartId);
        $items = $oldQuote->getAllVisibleItems();

        // Set coupon code in present in old quote
        if($oldQuote->getCouponCode()){
            $newQuote->setCouponCode($oldQuote->getCouponCode());
        }
        // Add products to new quote
        $cartItems = $this->addItemsToCart($items, $newQuote, $quoteIdMask->getMaskedId());
        $newQuote->collectTotals();
        $newQuote->save();
        $duplicateQuoteResponse = $this->duplicatedata->create();
        $duplicateQuoteResponse->setId((int) $newQuote->getEntityId());
        $duplicateQuoteResponse->setItems($cartItems);

        return $duplicateQuoteResponse;

    }

    /**
     * merge cart.
     *
     * @api
     * @param mixed $data
     * @return $this
    */
    public function mergeCart($data)
    {
        $guestQuoteId = $data['quote_id'];
        $customerId = $data['customer_id'];
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($guestQuoteId, 'masked_id');
        $guestQuote = $this->quoteRepository->get($quoteIdMask->getQuoteId());
        $quote = $this->quoteFactory->create()->loadByCustomer($customerId);
        $cartItems = $quote->getAllVisibleItems();
        
        foreach ($cartItems as $item) {
            if($item->getPrice() == 0){
                $quoteItem = $this->item->load($item->getItemId());
                $quoteItem->delete();
            }
        }

        if ($quote->getEntityId()) {
            if ($quote->merge($guestQuote)) {
                try {
                    $quote->setCouponCode($coupon)->collectTotals()->save();
                    $this->saveHandler->save($quote);
                    $quote->collectTotals();
                    $guestQuote->delete();
                    return true;
                } catch (\Exception $e) {
                    throw new Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * Get guest cart details by quote id.
     *
     * @param string $quote_id
     * @return $this
    **/
    public function getGuestCartDetails($quote_id)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote_id, 'masked_id');
        $quote = $this->quoteFactory->create()->load($quoteIdMask->getQuoteId());
        if ($quote->getId() && $quote->getIsActive() == 1) {
            $object = $this->setCartItemData($quote, $quote_id);
            return $object;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('You have no items in your shopping cart.')
            );
        }

    }

    /**
     * Get cart details by quote id.
     *
     * @param string $quote_id
     * @return $this
    **/
    public function cartById($quote_id)
    {
        $quote = $this->quoteFactory->create()->load($quote_id);
        if ($quote->getId() && $quote->getIsActive() == 1) {
            $object = $this->setCartItemData($quote, $quote_id);
            return $object;
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('You have no items in your shopping cart.')
            );
        }

    }

    /**
     * @param int $customer_id
     * @return $this
    **/
    public function getCartDetails($customer_id)
    {
        $customer = $this->customerFactory->create()->load($customer_id);
        $quote = $this->quoteFactory->create()->loadByCustomer($customer);
        if ($quote->getId()) {
            $object = $this->setCartItemData($quote, $quote->getId());
            return $object;   
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('You have no items in your shopping cart.')
            );
        }
    }
}
