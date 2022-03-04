<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Ui\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\ComponentVisibilityInterface;

/**
 * Seller addresses fieldset class
 */
class AddressFieldset extends \Magento\Ui\Component\Form\Fieldset implements ComponentVisibilityInterface
{
    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;

        parent::__construct($context, $components, $data);
    }

    /**
     * Can show seller addresses tab in tabs or not
     *
     * Will return false for not registered seller in a case when admin user created new seller account.
     * Needed to hide addresses tab from create new seller page
     *
     * @return boolean
     */
    public function isComponentVisible(): bool
    {
        $sellerId = $this->context->getRequestParam('id');
        return (bool)$sellerId;
    }
}
