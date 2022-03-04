<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Block\Adminhtml\Form\Element\Newsletter\Subscriptions as SubscriptionsElement;
use Magento\Seller\Controller\RegistryConstants;
use Magento\Seller\Model\Config\Share;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Seller account form block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Newsletter extends Generic implements TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Seller::tab/newsletter.phtml';

    /**
     * @var SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var AccountManagementInterface
     */
    protected $sellerAccountManagement;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param AccountManagementInterface $sellerAccountManagement
     * @param SystemStore $systemStore
     * @param SellerRepositoryInterface $sellerRepository
     * @param Share $shareConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        AccountManagementInterface $sellerAccountManagement,
        SystemStore $systemStore,
        SellerRepositoryInterface $sellerRepository,
        Share $shareConfig,
        array $data = []
    ) {
        $this->_subscriberFactory = $subscriberFactory;
        $this->sellerAccountManagement = $sellerAccountManagement;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->systemStore = $systemStore;
        $this->sellerRepository = $sellerRepository;
        $this->shareConfig = $shareConfig;
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Newsletter');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Newsletter');
    }

    /**
     * @inheritdoc
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return (bool)$this->getCurrentSellerId();
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $this->initForm();

        return $this;
    }

    /**
     * Init form values
     *
     * @return $this
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_newsletter');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Newsletter Information'),
                'class' => 'seller-newsletter-fieldset' . (!$this->isSingleWebsiteMode() ? ' multi-website' : ''),
            ]
        );

        $sellerSubscriptions = $this->getSellerSubscriptionsOnWebsites();
        if (empty($sellerSubscriptions)) {
            return $this;
        }

        if ($this->isSingleWebsiteMode()) {
            $this->prepareFormSingleWebsite($fieldset, $sellerSubscriptions);
            $this->updateFromSession($form, $this->getCurrentSellerId());
        } else {
            $this->prepareFormMultiplyWebsite($fieldset, $sellerSubscriptions);
        }

        if ($this->sellerAccountManagement->isReadonly($this->getCurrentSellerId())) {
            $fieldset->setReadonly(true, true);
        }

        return $this;
    }

    /**
     * Prepare form fields for single website mode
     *
     * @param Fieldset $fieldset
     * @param array $subscriptions
     * @return void
     */
    private function prepareFormSingleWebsite(Fieldset $fieldset, array $subscriptions): void
    {
        $seller = $this->getCurrentSeller();
        $websiteId = (int)$this->_storeManager->getStore($seller->getStoreId())->getWebsiteId();
        $sellerSubscription = $subscriptions[$websiteId] ?? $this->retrieveSubscriberData($seller, $websiteId);

        $checkboxElement = $fieldset->addField(
            'subscription_status_' . $websiteId,
            'checkbox',
            [
                'label' => __('Subscribed to Newsletter'),
                'name' => "subscription_status[$websiteId]",
                'data-form-part' => $this->getData('target_form'),
                'value' => $sellerSubscription['status'],
                'onchange' => 'this.value = this.checked;',
            ]
        );
        $checkboxElement->setIsChecked($sellerSubscription['status']);
        if (!$this->isSingleStoreMode()) {
            $fieldset->addField(
                'subscription_store_' . $websiteId,
                'select',
                [
                    'label' => __('Subscribed on Store View'),
                    'name' => "subscription_store[$websiteId]",
                    'data-form-part' => $this->getData('target_form'),
                    'values' => $sellerSubscription['store_options'],
                    'value' => $sellerSubscription['store_id'] ?? null,
                ]
            );
        }
        if (!empty($sellerSubscription['last_updated'])) {
            $text = $sellerSubscription['status'] ? __('Last Date Subscribed') : __('Last Date Unsubscribed');
            $fieldset->addField(
                'change_status_date_' . $websiteId,
                'label',
                [
                    'label' => $text,
                    'value' => $sellerSubscription['last_updated'],
                    'bold' => true
                ]
            );
        }
    }

    /**
     * Prepare form fields for multiply website mode
     *
     * @param Fieldset $fieldset
     * @param array $subscriptions
     * @return void
     */
    private function prepareFormMultiplyWebsite(Fieldset $fieldset, array $subscriptions): void
    {
        $fieldset->addType('seller_subscription', SubscriptionsElement::class);
        $fieldset->addField(
            'subscription',
            'seller_subscription',
            [
                'label' => __('Subscribed to Newsletter'),
                'name' => 'subscription',
                'subscriptions' => $subscriptions,
                'target_form' => $this->getData('target_form'),
                'class' => 'newsletter-subscriptions',
                'seller_id' => $this->getCurrentSellerId(),
            ]
        );
    }

    /**
     * Get current seller id
     *
     * @return int
     */
    private function getCurrentSellerId(): int
    {
        return (int)$this->_coreRegistry->registry(RegistryConstants::CURRENT_SELLER_ID);
    }

    /**
     * Get current seller model
     *
     * @return SellerInterface|null
     */
    private function getCurrentSeller(): ?SellerInterface
    {
        $sellerId = $this->getCurrentSellerId();
        try {
            $seller = $this->sellerRepository->getById($sellerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $seller;
    }

    /**
     * Get Seller Subscriptions on Websites
     *
     * @return array
     */
    private function getSellerSubscriptionsOnWebsites(): array
    {
        $seller = $this->getCurrentSeller();
        if ($seller === null) {
            return [];
        }

        $subscriptions = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            /** Skip websites without stores */
            if ($website->getStoresCount() === 0) {
                continue;
            }
            $websiteId = (int)$website->getId();
            $subscriptions[$websiteId] = $this->retrieveSubscriberData($seller, $websiteId);
        }

        return $subscriptions;
    }

    /**
     * Retrieve subscriber data
     *
     * @param SellerInterface $seller
     * @param int $websiteId
     * @return array
     */
    private function retrieveSubscriberData(SellerInterface $seller, int $websiteId): array
    {
        $subscriber = $this->_subscriberFactory->create()->loadBySeller((int)$seller->getId(), $websiteId);
        $storeOptions = $this->systemStore->getStoreOptionsTree(false, [], [], [$websiteId]);
        $subscriberData = $subscriber->getData();
        $subscriberData['last_updated'] = $this->getSubscriberStatusChangeDate($subscriber);
        $subscriberData['website_id'] = $websiteId;
        $subscriberData['website_name'] = $this->systemStore->getWebsiteName($websiteId);
        $subscriberData['status'] = $subscriber->isSubscribed();
        $subscriberData['store_options'] = $storeOptions;

        return $subscriberData;
    }

    /**
     * Is single systemStore mode
     *
     * @return bool
     */
    private function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Is single website mode
     *
     * @return bool
     */
    private function isSingleWebsiteMode(): bool
    {
        return $this->isSingleStoreMode()
            || !$this->shareConfig->isGlobalScope()
            || count($this->_storeManager->getWebsites()) === 1;
    }

    /**
     * Update form elements from session data
     *
     * @param Form $form
     * @param int $sellerId
     * @return void
     */
    protected function updateFromSession(Form $form, $sellerId)
    {
        if (!$this->isSingleWebsiteMode()) {
            return;
        }
        $data = $this->_backendSession->getSellerFormData();
        $sessionSellerId = $data['seller']['entity_id'] ?? null;
        if ($sessionSellerId === null || (int)$sessionSellerId !== (int)$sellerId) {
            return;
        }

        $websiteId = (int)$this->getCurrentSeller()->getWebsiteId();
        $statusSessionValue = $data['subscription_status'][$websiteId] ?? null;
        if ($statusSessionValue !== null) {
            $subscribeElement = $form->getElement('subscription_status_' . $websiteId);
            $subscribeElement->setValue($statusSessionValue);
            $subscribeElement->setChecked($statusSessionValue);
        }
        $storeSessionValue = $data['subscription_store'][$websiteId] ?? null;
        $storeElement = $form->getElement('subscription_store_' . $websiteId);
        if ($storeSessionValue !== null && $storeElement !== null) {
            $storeElement->setValue($storeSessionValue);
        }
    }

    /**
     * Retrieve the date when the subscriber status changed.
     *
     * @return null|string
     */
    public function getStatusChangedDate()
    {
        $seller = $this->getCurrentSellerId();
        if ($seller === null) {
            return '';
        }
        $sellerId = (int)$seller->getId();
        $subscriber = $this->_subscriberFactory->create()->loadBySeller($sellerId, (int)$seller->getWebsiteId());

        return $this->getSubscriberStatusChangeDate($subscriber);
    }

    /**
     * Retrieve the date when the subscriber status changed
     *
     * @param Subscriber $subscriber
     * @return string
     */
    private function getSubscriberStatusChangeDate(Subscriber $subscriber): string
    {
        if (empty($subscriber->getChangeStatusAt())) {
            return '';
        }

        return $this->formatDate(
            $subscriber->getChangeStatusAt(),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }
}
