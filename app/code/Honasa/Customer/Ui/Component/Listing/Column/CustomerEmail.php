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

use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Ui\Component\Listing\Columns\Column;
use Honasa\Customer\Helper\Data as Helper;

class CustomerEmail extends Column
{
    protected $helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Helper $helper,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->helper->getEmailMask($item[$this->getData('name')]);
            }
        }
        return $dataSource;
    }
}
