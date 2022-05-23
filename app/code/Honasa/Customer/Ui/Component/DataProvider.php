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
namespace Honasa\Customer\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Honasa\Customer\Helper\Data as Helper;

class DataProvider extends \Magento\Customer\Ui\Component\DataProvider
{
    
    protected $helper;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        AttributeRepository $attributeRepository,
        Helper $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $attributeRepository,
            $meta,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = parent::getData();
        foreach ($data['items'] as &$item) {
            $item['email'] = $this->helper->getEmailMask($item['email']);
            $item['mobile_number'] = $this->helper->getPhonenoMask($item['mobile_number']);
            if(isset($item['billing_telephone'])){
                $item['billing_telephone'] = $this->helper->getPhonenoMask($item['billing_telephone']);
            }
        }
        return $data;
    }
}
