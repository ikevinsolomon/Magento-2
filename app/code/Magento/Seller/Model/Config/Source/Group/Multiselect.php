<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Config\Source\Group;

use Magento\Seller\Model\Seller\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Seller\Api\GroupManagementInterface;
use Magento\Framework\App\ObjectManager;

class Multiselect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Seller groups options array
     *
     * @var null|array
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
     * @param GroupSourceLoggedInOnlyInterface|null $groupSourceLoggedInOnly
     */
    public function __construct(
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        GroupSourceLoggedInOnlyInterface $groupSourceLoggedInOnly = null
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        $this->groupSourceLoggedInOnly = $groupSourceLoggedInOnly
            ?: ObjectManager::getInstance()->get(GroupSourceLoggedInOnlyInterface::class);
    }

    /**
     * Retrieve seller groups as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = $this->groupSourceLoggedInOnly->toOptionArray();
        }
        return $this->_options;
    }
}
