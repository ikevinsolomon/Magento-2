<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Config\Source;

use Magento\Seller\Api\GroupManagementInterface;
use Magento\Seller\Model\Seller\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Framework\App\ObjectManager;

class Group implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @deprecated 101.0.0
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @deprecated 101.0.0
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_converter;

    /**
     * @var GroupSourceLoggedInOnlyInterface
     */
    private $groupSourceLoggedInOnly;

    /**
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Convert\DataObject $converter
     * @param GroupSourceLoggedInOnlyInterface $groupSourceForLoggedInSellers
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        GroupSourceLoggedInOnlyInterface $groupSourceForLoggedInSellers = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        $this->groupSourceLoggedInOnly = $groupSourceForLoggedInSellers
            ?: ObjectManager::getInstance()->get(GroupSourceLoggedInOnlyInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->groupSourceLoggedInOnly->toOptionArray();
            array_unshift($this->_options, ['value' => '', 'label' => __('-- Please Select --')]);
        }

        return $this->_options;
    }
}
