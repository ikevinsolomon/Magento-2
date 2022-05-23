<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Honasa\Quote\Model;

use \Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management object.
 */
class CouponManagement extends \Magento\Quote\Model\CouponManagement
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        return $quote->getCouponCode();
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $items = $quote->getAllVisibleItems();
        $catids = array();
        // Check product category is gift packs or tuff
        $checkProduct = 0;
        $pre_added_product = 0;
        $pre_added_product2 = 0;
        $pre_added_product3 = 0;
        $pre_added_product4 = 0;
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $categories = $product->getCategoryIds();
            $catids = array_merge($catids, $categories);
            $proPrice = $item->getPrice();
            // gift pack & TUFF condition
            //in_array(7, $categories) || 

            if (in_array(37, $product->getCategoryIds())) {
                $checkProduct = 1;
            }
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $productSkuConfig = $scopeConfig->getValue('pwa/auto_add/auto_add_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productSkuArray= explode(",",$productSkuConfig);
        $couponConfig = $scopeConfig->getValue('pwa/auto_add/coupon_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $couponArray= explode(",",$couponConfig);
        $numberOfProducts = $scopeConfig->getValue('pwa/auto_add/no_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         //for second product coupon
        $productSecSkuConfig = $scopeConfig->getValue('pwa/autoadd_config/auto_add_sec_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productSecSkuArray= explode(",",$productSecSkuConfig);
        $couponSecConfig = $scopeConfig->getValue('pwa/autoadd_config/coupon_sec_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $couponSecArray= explode(",",$couponSecConfig);
        $numberOfSecProducts = $scopeConfig->getValue('pwa/autoadd_config/no_sec_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //for Third product coupon
        $productThrSkuConfig = $scopeConfig->getValue('pwa/auto_thr_add/auto_add_thr_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productThrSkuArray= explode(",",$productThrSkuConfig);
        $couponThrConfig = $scopeConfig->getValue('pwa/auto_thr_add/coupon_thr_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $couponThrArray= explode(",",$couponThrConfig);
        $numberOfThrProducts = $scopeConfig->getValue('pwa/auto_thr_add/no_thr_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        //for Fourth product coupon
        $productForSkuConfig = $scopeConfig->getValue('pwa/auto_for_add/auto_add_for_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productForSkuArray= explode(",",$productForSkuConfig);
        $couponForConfig = $scopeConfig->getValue('pwa/auto_for_add/coupon_for_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $couponForArray= explode(",",$couponForConfig);
        $numberOfForProducts = $scopeConfig->getValue('pwa/auto_for_add/no_for_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $cartStatus = $scopeConfig->getValue('pwa/auto_add/status_coupon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $categories = $product->getCategoryIds();

            $catids = array_merge($catids, $categories);
            if(in_array($item->getSku(), $productSkuArray)){
                $pre_added_product+=1;
            }
            if(in_array($item->getSku(), $productSecSkuArray)){
                $pre_added_product2 +=1;
            }
            if(in_array($item->getSku(), $productThrSkuArray)){
                $pre_added_product3 +=1;
            }
            if(in_array($item->getSku(), $productForSkuArray)){
                $pre_added_product4 +=1;
            }
        }
        $categoryids = array_unique($catids);
        $target = array(37);
        if ($checkProduct) {
            throw new NoSuchEntityException(__('Sorry could not apply coupon on sample product and Gift packs'));
        }
        if (count(array_intersect($categoryids, $target)) > 0) {
            throw new NoSuchEntityException(__('Coupon code is not valid'));
        }

        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not apply coupon code'));
        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__('Coupon code is not valid'));
        }
        if($pre_added_product == 0 && in_array(strtolower($quote->getCouponCode()),$couponArray)){   
            $k = 0;
            while($numberOfProducts){
                $quote1 = $this->quoteRepository->getActive($cartId);
                $product1 = $this->productRepository->get($productSkuArray[$k]);
                $k++;
                $quote1->addProduct($product1, intval(1));  
                $this->quoteRepository->save($quote1);
                $quote1->collectTotals();
                $numberOfProducts-- ;
            }
        }
        //for second product
        if($pre_added_product2 == 0 && in_array(strtolower($quote->getCouponCode()),$couponSecArray)){   
            $k = 0;
            while($numberOfSecProducts){
                $quote1 = $this->quoteRepository->getActive($cartId);
                $product1 = $this->productRepository->get($productSecSkuArray[$k]);
                $k++;
                $quote1->addProduct($product1, intval(1));  
                $this->quoteRepository->save($quote1);
                $quote1->collectTotals();
                $numberOfSecProducts-- ;
            }
        }
        //for third product
        if($pre_added_product3 == 0 && in_array(strtolower($quote->getCouponCode()),$couponThrArray)){   
            $k = 0;
            while($numberOfThrProducts){
                $quote1 = $this->quoteRepository->getActive($cartId);
                $product1 = $this->productRepository->get($productThrSkuArray[$k]);
                $k++;
                $quote1->addProduct($product1, intval(1));  
                $this->quoteRepository->save($quote1);
                $quote1->collectTotals();
                $numberOfThrProducts-- ;
            }
        }
        //for Fourth product
        if($pre_added_product4 == 0 && in_array(strtolower($quote->getCouponCode()),$couponForArray)){   
            $k = 0;
            while($numberOfForProducts){
                $quote1 = $this->quoteRepository->getActive($cartId);
                $product1 = $this->productRepository->get($productForSkuArray[$k]);
                $k++;
                $quote1->addProduct($product1, intval(1));  
                $this->quoteRepository->save($quote1);
                $quote1->collectTotals();
                $numberOfForProducts-- ;
            }
        }
      
        if($pre_added_product >= 1 && !in_array(strtolower($quote->getCouponCode()),$couponArray))
        {
            if(!$cartStatus)
            {
                if(empty($quote->getCouponCode())){
                    $quote2 = $this->quoteRepository->getActive($cartId);
                    $items = $quote->getAllVisibleItems();
                    foreach ($items as $item) {
                        if(in_array($item->getSku(), $productSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponArray))
                        {
                            $itemid = $item->getItemId();
                            $quote2->removeItem($itemid)->save();
                        }
                    }
                    $this->quoteRepository->save($quote2->collectTotals());
                  }
            }
        }

        //for second product
        if($pre_added_product2 >= 1 && !in_array(strtolower($quote->getCouponCode()),$couponSecArray))
        {
            if(empty($quote->getCouponCode())){
            $quote2 = $this->quoteRepository->getActive($cartId);
            $items = $quote->getAllVisibleItems();
            foreach ($items as $item) {
                if(in_array($item->getSku(), $productSecSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponSecArray))
                {
                    $itemid = $item->getItemId();
                    $quote2->removeItem($itemid)->save();
                }
            }
            $this->quoteRepository->save($quote2->collectTotals());
          }
        }
        //for third product
        if($pre_added_product3 >= 1 && !in_array(strtolower($quote->getCouponCode()),$couponThrArray))
        {
            if(empty($quote->getCouponCode())){
            $quote2 = $this->quoteRepository->getActive($cartId);
            $items = $quote->getAllVisibleItems();
            foreach ($items as $item) {
                if(in_array($item->getSku(), $productThrSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponThrArray))
                {
                    $itemid = $item->getItemId();
                    $quote2->removeItem($itemid)->save();
                }
            }
            $this->quoteRepository->save($quote2->collectTotals());
         }
        }
         //for Fourth product
         if($pre_added_product4 >= 1 && !in_array(strtolower($quote->getCouponCode()),$couponForArray))
         {
            if(empty($quote->getCouponCode())){
             $quote2 = $this->quoteRepository->getActive($cartId);
             $items = $quote->getAllVisibleItems();
             foreach ($items as $item) {
                 if(in_array($item->getSku(), $productForSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponForArray))
                 {
                     $itemid = $item->getItemId();
                     $quote2->removeItem($itemid)->save();
                 }
             }
             $this->quoteRepository->save($quote2->collectTotals());
            }
         }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setCouponCode('');
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        if ($quote->getCouponCode() != '') {
            throw new CouldNotDeleteException(__('Could not delete coupon code'));
        }
        $items = $quote->getAllVisibleItems();
        $quote2 = $this->quoteRepository->getActive($cartId);
        $item = $quote->getAllVisibleItems();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $productSkuConfig = $scopeConfig->getValue('pwa/auto_add/auto_add_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productSkuArray= explode(",",$productSkuConfig);
        $couponConfig = $scopeConfig->getValue('pwa/auto_add/coupon_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $couponArray= explode(",",$couponConfig);
         //for second product coupon
         $productSecSkuConfig = $scopeConfig->getValue('pwa/autoadd_config/auto_add_sec_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         $productSecSkuArray= explode(",",$productSecSkuConfig);
         $couponSecConfig = $scopeConfig->getValue('pwa/autoadd_config/coupon_sec_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         $couponSecArray= explode(",",$couponSecConfig);
         //for Third product coupon
         $productThrSkuConfig = $scopeConfig->getValue('pwa/auto_thr_add/auto_add_thr_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         $productThrSkuArray= explode(",",$productThrSkuConfig);
         $couponThrConfig = $scopeConfig->getValue('pwa/auto_thr_add/coupon_thr_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
         $couponThrArray= explode(",",$couponThrConfig);
         //for Fourth product coupon
        $productForSkuConfig = $scopeConfig->getValue('pwa/auto_for_add/auto_add_for_skus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productForSkuArray= explode(",",$productForSkuConfig);
        $couponForConfig = $scopeConfig->getValue('pwa/auto_for_add/coupon_for_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $couponForArray= explode(",",$couponForConfig);
        
        foreach ($items as $item) {
            if(!in_array($item->getSku(), $productSkuArray) && in_array(strtolower($quote->getCouponCode()),$couponArray))
            {
                $quote->setCouponCode('');
                $this->quoteRepository->save($quote->collectTotals());
            }
            if(in_array($item->getSku(), $productSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponArray))
            {
                $itemid = $item->getItemId();
                $quote2->removeItem($itemid)->save();
            }
            if(in_array($item->getSku(), $productSecSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponSecArray))
            {
                $itemid = $item->getItemId();
                $quote2->removeItem($itemid)->save();
            }
            if(in_array($item->getSku(), $productThrSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponThrArray))
            {
                $itemid = $item->getItemId();
                $quote2->removeItem($itemid)->save();
            }
            if(in_array($item->getSku(), $productForSkuArray) && !in_array(strtolower($quote->getCouponCode()),$couponForArray))
            {
                $itemid = $item->getItemId();
                $quote2->removeItem($itemid)->save();
            }
          
        }
         //lastest
        $this->quoteRepository->save($quote2->collectTotals());
        return true;
    }
}
