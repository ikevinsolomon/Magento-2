<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Model\Session;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Seller dashboard block
 *
 * @api
 * @since 100.0.2
 */
class Dashboard extends Template
{
    /**
     * @var Subscriber
     */
    protected $subscription;

    /**
     * @var Session
     */
    protected $sellerSession;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * @param Context $context
     * @param Session $sellerSession
     * @param SubscriberFactory $subscriberFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param AccountManagementInterface $sellerAccountManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $sellerSession,
        SubscriberFactory $subscriberFactory,
        SellerRepositoryInterface $sellerRepository,
        AccountManagementInterface $sellerAccountManagement,
        array $data = []
    ) {
        $this->sellerSession = $sellerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->sellerRepository = $sellerRepository;
        $this->sellerAccountManagement = $sellerAccountManagement;
        parent::__construct($context, $data);
    }

    /**
     * Return the Seller given the seller Id stored in the session.
     *
     * @return SellerInterface
     */
    public function getSeller()
    {
        return $this->sellerRepository->getById($this->sellerSession->getSellerId());
    }

    /**
     * Retrieve the Url for editing the seller's account.
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->_urlBuilder->getUrl('seller/account/edit', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for seller addresses.
     *
     * @return string
     */
    public function getAddressesUrl()
    {
        return $this->_urlBuilder->getUrl('seller/address/index', ['_secure' => true]);
    }

    /**
     * Retrieve the Url for editing the specified address.
     *
     * @param AddressInterface $address
     * @return string
     */
    public function getAddressEditUrl($address)
    {
        return $this->_urlBuilder->getUrl(
            'seller/address/edit',
            ['_secure' => true, 'id' => $address->getId()]
        );
    }

    /**
     * Retrieve the Url for seller orders.
     *
     * @return string
     * @deprecated 102.0.3 Action does not exist
     */
    public function getOrdersUrl()
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);
        return '';
    }

    /**
     * Retrieve the Url for managing seller wishlist.
     *
     * @return string
     */
    public function getWishlistUrl()
    {
        return $this->_urlBuilder->getUrl('wishlist/index', ['_secure' => true]);
    }

    /**
     * Retrieve the subscription object (i.e. the subscriber).
     *
     * @return Subscriber
     */
    public function getSubscriptionObject()
    {
        if ($this->subscription === null) {
            $websiteId = (int)$this->_storeManager->getWebsite()->getId();
            $this->subscription = $this->_createSubscriber();
            $this->subscription->loadBySeller((int)$this->getSeller()->getId(), $websiteId);
        }

        return $this->subscription;
    }

    /**
     * Retrieve the Url for managing newsletter subscriptions.
     *
     * @return string
     */
    public function getManageNewsletterUrl()
    {
        return $this->getUrl('newsletter/manage');
    }

    /**
     * Retrieve subscription text, either subscribed or not.
     *
     * @return Phrase
     */
    public function getSubscriptionText()
    {
        if ($this->getSubscriptionObject()->isSubscribed()) {
            return __('You are subscribed to our newsletter.');
        }

        return __('You aren\'t subscribed to our newsletter.');
    }

    /**
     * Retrieve the seller's primary addresses (i.e. default billing and shipping).
     *
     * @return AddressInterface[]|bool
     */
    public function getPrimaryAddresses()
    {
        $addresses = [];
        $sellerId = $this->getSeller()->getId();

        if ($defaultBilling = $this->sellerAccountManagement->getDefaultBillingAddress($sellerId)) {
            $addresses[] = $defaultBilling;
        }

        if ($defaultShipping = $this->sellerAccountManagement->getDefaultShippingAddress($sellerId)) {
            if ($defaultBilling) {
                if ($defaultBilling->getId() != $defaultShipping->getId()) {
                    $addresses[] = $defaultShipping;
                }
            } else {
                $addresses[] = $defaultShipping;
            }
        }

        return empty($addresses) ? false : $addresses;
    }

    /**
     * Get back Url in account dashboard.
     *
     * This method is copy/pasted in:
     * \Magento\Wishlist\Block\Seller\Wishlist  - Because of strange inheritance
     * \Magento\Seller\Block\Address\Book - Because of secure Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('seller/account/');
    }

    /**
     * Create an instance of a subscriber.
     *
     * @return Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->subscriberFactory->create();
    }
}
