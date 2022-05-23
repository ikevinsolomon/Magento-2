<?php
/**
 * Honasa_Customer
 *
 * @category  XML
 * @package   Honasa\Customer
 * @author    Honasa Consumer Pvt. Ltd
 * @copyright 2022 Copyright (c) Honasa Consumer Pvt Ltd
 * @link      https://www.mamaearth.in/
 */
namespace Honasa\Customer\Observer;

use Amasty\Feed\Api\Data\FeedInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerSave implements ObserverInterface
{
    /**
     * @var \Honasa\Customer\Helper\Data
     */
    private $helper;

    public function __construct(
        \Honasa\Customer\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        
        if(!$this->helper->checkPermission()) {
            $request = $observer->getEvent()->getRequest()->getParams();
            $customer = $observer->getEvent()->getCustomer();
            $customerModel = $this->customerFactory->create()->load($request['customer']['entity_id']);
            $request['customer']['email'] = $customerModel->getEmail();
            $request['customer']['mobile_number'] = $customerModel->getMobileNumber();
            $customer->setEmail($request['customer']['email']);
            $customer->setCustomAttribute('mobile_number', $request['customer']['mobile_number']);
            $observer->getEvent()->getRequest()->setParams($request);
        }
    }
}
