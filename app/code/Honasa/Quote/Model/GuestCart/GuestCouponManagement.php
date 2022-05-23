<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Honasa\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestCouponManagementInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\QuoteRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Coupon management class for guest carts.
 */
class GuestCouponManagement extends \Magento\Quote\Model\GuestCart\GuestCouponManagement
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    //protected $quoteFactory;

    protected $quoteRepository;
    protected $productRepository;

    /**
     * Constructs a coupon read service object.
     *
     * @param CouponManagementInterface $couponManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CouponManagementInterface $couponManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        //\Magento\Quote\Model\QuoteFactory $quoteFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository

    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->couponManagement = $couponManagement;
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->couponManagement->get($quoteIdMask->getQuoteId());
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        $items = $quote->getAllVisibleItems();
        // Check product category is gift packs or tuff
        $checkProduct = 0;
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $categories = $product->getCategoryIds();
            $proPrice = $item->getPrice();
            if (in_array(37, $product->getCategoryIds())) {
                $checkProduct = 1;
            }

        }
        if ($checkProduct) {
            throw new NoSuchEntityException(__('Sorry could not apply coupon on sample product and Gift packs'));
        }

        return $this->couponManagement->set($quoteIdMask->getQuoteId(), $couponCode);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->couponManagement->remove($quoteIdMask->getQuoteId());
    }
}
