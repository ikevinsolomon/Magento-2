<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Account\Dashboard;

use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Block\Form\Register;
use Magento\Seller\Helper\Session\CurrentSeller;
use Magento\Seller\Helper\View;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Dashboard Seller Info
 *
 * @api
 * @since 100.0.2
 */
class Info extends Template
{
    /**
     * Cached subscription object
     *
     * @var Subscriber
     */
    protected $_subscription;

    /**
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var View
     */
    protected $_helperView;

    /**
     * @var CurrentSeller
     */
    protected $currentSeller;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CurrentSeller $currentSeller
     * @param SubscriberFactory $subscriberFactory
     * @param View $helperView
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrentSeller $currentSeller,
        SubscriberFactory $subscriberFactory,
        View $helperView,
        array $data = []
    ) {
        $this->currentSeller = $currentSeller;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_helperView = $helperView;
        parent::__construct($context, $data);
    }

    /**
     * Returns the Magento Seller Model for this block
     *
     * @return SellerInterface|null
     */
    public function getSeller()
    {
        try {
            return $this->currentSeller->getSeller();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get the full name of a seller
     *
     * @return string full name
     */
    public function getName()
    {
        return $this->_helperView->getSellerName($this->getSeller());
    }

    /**
     * Get the url to change password
     *
     * @return string
     */
    public function getChangePasswordUrl()
    {
        return $this->_urlBuilder->getUrl('seller/account/edit/changepass/1');
    }

    /**
     * Get Seller Subscription Object Information
     *
     * @return Subscriber
     */
    public function getSubscriptionObject()
    {
        if (!$this->_subscription) {
            $this->_subscription = $this->_createSubscriber();
            $seller = $this->getSeller();
            if ($seller) {
                $websiteId = (int)$this->_storeManager->getWebsite()->getId();
                $this->_subscription->loadBySeller((int)$seller->getId(), $websiteId);
            }
        }
        return $this->_subscription;
    }

    /**
     * Gets Seller subscription status
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->getLayout()
            ->getBlockSingleton(Register::class)
            ->isNewsletterEnabled();
    }

    /**
     * Create new instance of Subscriber
     *
     * @return Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        return $this->currentSeller->getSellerId() ? parent::_toHtml() : '';
    }
}
