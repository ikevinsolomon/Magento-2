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
namespace Honasa\Customer\Ui\Component\Listing\Column;

use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Ui\Component\Listing\Columns\Column;
use Honasa\Customer\Helper\Data as Helper;

class Telephone extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        Helper $helper,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria = $criteria;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {

                $order = $this->_orderRepository->get($item["entity_id"]);
                $telephone = $order->getBillingAddress()->getTelephone();
                $item[$this->getData('name')] = $this->helper->getPhonenoMask($telephone);
            }
        }
        return $dataSource;
    }
}
