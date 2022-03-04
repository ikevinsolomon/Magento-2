<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Ui\Component\Listing\Address\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Prepare actions column for seller addresses grid
 */
class Actions extends Column
{
    const SELLER_ADDRESS_PATH_DELETE = 'seller/address/delete';
    const SELLER_ADDRESS_PATH_DEFAULT_SHIPPING = 'seller/address/defaultShippingAddress';
    const SELLER_ADDRESS_PATH_DEFAULT_BILLING = 'seller/address/defaultBillingAddress';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'callback' => [
                            [
                                'provider' => 'seller_form.areas.address.address'
                                    . '.seller_address_update_modal.update_seller_address_form_loader',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'seller_form.areas.address.address'
                                    . '.seller_address_update_modal',
                                'target' => 'openModal',
                            ],
                            [
                                'provider' => 'seller_form.areas.address.address'
                                    . '.seller_address_update_modal.update_seller_address_form_loader',
                                'target' => 'render',
                                'params' => [
                                    'entity_id' => $item['entity_id'],
                                ],
                            ]
                        ],
                        'href' => '#',
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];

                    $item[$name]['setDefaultBilling'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::SELLER_ADDRESS_PATH_DEFAULT_BILLING,
                            ['parent_id' => $item['parent_id'], 'id' => $item['entity_id']]
                        ),
                        'label' => __('Set as default billing'),
                        'isAjax' => true,
                        'confirm' => [
                            'title' => __('Set address as default billing'),
                            'message' => __('Are you sure you want to set the address as default billing address?')
                        ]
                    ];

                    $item[$name]['setDefaultShipping'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::SELLER_ADDRESS_PATH_DEFAULT_SHIPPING,
                            ['parent_id' => $item['parent_id'], 'id' => $item['entity_id']]
                        ),
                        'label' => __('Set as default shipping'),
                        'isAjax' => true,
                        'confirm' => [
                            'title' => __('Set address as default shipping'),
                            'message' => __('Are you sure you want to set the address as default shipping address?')
                        ]
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::SELLER_ADDRESS_PATH_DELETE,
                            ['parent_id' => $item['parent_id'], 'id' => $item['entity_id']]
                        ),
                        'label' => __('Delete'),
                        'isAjax' => true,
                        'confirm' => [
                            'title' => __('Delete address'),
                            'message' => __('Are you sure you want to delete the address?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
