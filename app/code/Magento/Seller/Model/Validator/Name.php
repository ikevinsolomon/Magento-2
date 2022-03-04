<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Validator;

use Magento\Seller\Model\Seller;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Seller name fields validator.
 */
class Name extends AbstractValidator
{
    private const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'\s\d]){1,255}+/u';

    /**
     * Validate name fields.
     *
     * @param Seller $seller
     * @return bool
     */
    public function isValid($seller)
    {
        if (!$this->isValidName($seller->getFirstname())) {
            parent::_addMessages([['firstname' => 'First Name is not valid!']]);
        }

        if (!$this->isValidName($seller->getLastname())) {
            parent::_addMessages([['lastname' => 'Last Name is not valid!']]);
        }

        if (!$this->isValidName($seller->getMiddlename())) {
            parent::_addMessages([['middlename' => 'Middle Name is not valid!']]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check if name field is valid.
     *
     * @param string|null $nameValue
     * @return bool
     */
    private function isValidName($nameValue)
    {
        if ($nameValue != null) {
            if (preg_match(self::PATTERN_NAME, $nameValue, $matches)) {
                return $matches[0] == $nameValue;
            }
        }

        return true;
    }
}
