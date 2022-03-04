<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Ui\Component\Listing\Column\Online\Type;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [
                [
                    'value' => \Magento\Seller\Model\Visitor::VISITOR_TYPE_VISITOR,
                    'label' => __('Visitor'),
                ],
                [
                    'value' => \Magento\Seller\Model\Visitor::VISITOR_TYPE_SELLER,
                    'label' => __('Seller'),
                ]
            ];
        }
        return $this->options;
    }
}
