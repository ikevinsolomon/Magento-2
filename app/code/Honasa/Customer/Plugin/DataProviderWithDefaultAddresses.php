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
namespace Honasa\Customer\Plugin;

class DataProviderWithDefaultAddresses
{
    protected $helper;

    public function __construct(
        \Honasa\Customer\Helper\Data $helper
    ) {
       $this->helper = $helper;
    }

    public function afterGetData(
        \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $subject,
        $result
    ) {
        foreach($result as $i => &$customer) {
            if(isset($customer['customer']['email'])) {
                $customer['customer']['email'] = $this->helper->getEmailMask($customer['customer']['email']);
            }
            if(isset($customer['customer']['mobile_number'])) {
                $customer['customer']['mobile_number'] = $this->helper->getPhonenoMask($customer['customer']['mobile_number']);
            }
            if(isset($customer['default_billing_address']['telephone'])) {
                $customer['default_billing_address']['telephone'] = $this->helper->getPhonenoMask($customer['default_billing_address']['telephone']);
            }
            if(isset($customer['default_shipping_address']['telephone'])) {
                $customer['default_shipping_address']['telephone'] = $this->helper->getPhonenoMask($customer['default_shipping_address']['telephone']);
            }
        }
        return $result;
    }
}
