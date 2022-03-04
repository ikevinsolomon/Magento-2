<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Seller\Attribute\Source;

/**
 * Seller website attribute source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_store;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Magento\Store\Model\System\Store $store
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Store\Model\System\Store $store
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->_store = $store;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->_store->getWebsiteValuesForForm();
        }

        return $this->_options;
    }

    /**
     * @param int|string $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        if (!$this->_options) {
            $this->_options = $this->getAllOptions();
        }
        foreach ($this->_options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
