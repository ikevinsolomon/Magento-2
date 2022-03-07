<?php

namespace Honasa\DisableFrontend\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Custom implements ArrayInterface
{

    /**
     * Options for the admin config
     *
     * @return array
     * @author Abel Bolanos Martinez <abelbmartinez@gmail.com>
     */
    public function toOptionArray()
    {

        return [
            ['value' => 0, 'label' => __('Blank Page')],
            ['value' => 1, 'label' => __('Admin')],
        ];
    }
}
