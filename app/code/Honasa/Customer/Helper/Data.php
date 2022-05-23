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
namespace Honasa\Customer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Data extends AbstractHelper implements ArgumentInterface
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Authorization $authorization
    ) {
        $this->authorization = $authorization;
        parent::__construct($context);
    }

    public function getEmailMask($email) {
        if($this->checkPermission()) {
            return $email;
        }
        $subStr = explode('@', $email);
        return substr($email, 0, 1).'********@'.$subStr[1];
    }

    public function getPhonenoMask($phoneno) {
        if($this->checkPermission()) {
            return $phoneno;
        }
        return '********'.substr($phoneno, -2);
    }

    public function checkPermission()
    {
        return $this->authorization->isAllowed('Honasa_Customer::config_honasa_Customer');
    }
}