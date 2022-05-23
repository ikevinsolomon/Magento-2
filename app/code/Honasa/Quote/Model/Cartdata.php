<?php

namespace Honasa\Quote\Model;

class Cartdata extends \Magento\Framework\Model\AbstractModel implements \Honasa\Quote\Api\Data\CartdataInterface
{
    const KEY_ID = 'id';
    const KEY_CREATED_AT = 'created_at';
    const KEY_UPDATED_AT = 'updated_at';
    const KEY_IS_ACTIVE = 'is_active';
    public $totals;
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
     * @return string
     */

    public function getCreatedAt()
    {
        return $this->_getData(self::KEY_CREATED_AT);
    }
    /**
     * Set name
     *
     * @param string $createat
     * @return $this
     */
    public function setCreatedAt($createat)
    {
        return $this->setData(self::KEY_CREATED_AT, $createat);
    }
    /**
     * @return string
     */

    public function getUpdatedAt()
    {
        return $this->_getData(self::KEY_UPDATED_AT);
    }
    /**
     * Set name
     *
     * @param string $updatedat
     * @return $this
     */

    public function setUpdatedAt($updatedat)
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedat);
    }
    /**
     * @return string
     */

    public function getIsActive()
    {
        return $this->_getData(self::KEY_IS_ACTIVE);
    }
    /**
     * Set name
     *
     * @param string $isactive
     * @return $this
     */

    public function setIsActive($isactive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isactive);
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

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     */
    public function getTotals()
    {
        return $this->totals;
    }
    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $totals.
     * @return $this
     */
    public function setTotals(\Magento\Quote\Api\Data\TotalsInterface $totals = null)
    {
        $this->totals = $totals;
        return $this;
    }

}
