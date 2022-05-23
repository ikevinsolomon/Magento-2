<?php

namespace Honasa\Quote\Model;

class Duplicatecart extends \Magento\Framework\Model\AbstractModel implements \Honasa\Quote\Api\Data\DuplicatecartInterface
{
    const KEY_ID = 'id';
    public $items;
    private $rowset = [];
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return int
     */

    public function getId()
    {
        return $this->_getData(self::KEY_ID);
    }
    /**
     * Set name
     *
     * @param int $id
     * @return $this
     */

    public function setId($id)
    {
        return $this->setData(self::KEY_ID, $id);
    }

    /**
     * @return array[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

}
