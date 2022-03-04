<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit\Address;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Seller\Ui\Component\Listing\Address\Column\Actions;

/**
 * Delete button on edit seller address form
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get delete button data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getAddressId()) {
            $data = [
                'label' => __('Delete'),
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'seller_address_form.seller_address_form',
                                    'actionName' => 'deleteAddress',
                                    'params' => [
                                        $this->getDeleteUrl(),
                                    ],

                                ]
                            ],
                        ],
                    ],
                ],
                'sort_order' => 20
            ];
        }
        return $data;
    }

    /**
     * Get delete button url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl(
            Actions::SELLER_ADDRESS_PATH_DELETE,
            ['parent_id' => $this->getSellerId(), 'id' => $this->getAddressId()]
        );
    }
}
