<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Indexer;

use Magento\Seller\Model\ResourceModel\Seller\Indexer\CollectionFactory;
use Magento\Seller\Model\ResourceModel\Seller\Indexer\Collection;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Traversable;

/**
 * Sellers data batch generator for seller_grid indexer
 */
class Source implements \IteratorAggregate, \Countable, SourceProviderInterface
{
    /**
     * @var Collection
     */
    private $sellerCollection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param CollectionFactory $collectionFactory
     * @param int $batchSize
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $batchSize = 10000
    ) {
        $this->sellerCollection = $collectionFactory->create();
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function getMainTable()
    {
        return $this->sellerCollection->getMainTable();
    }

    /**
     * @inheritdoc
     */
    public function getIdFieldName()
    {
        return $this->sellerCollection->getIdFieldName();
    }

    /**
     * @inheritdoc
     */
    public function addFieldToSelect($fieldName, $alias = null)
    {
        $this->sellerCollection->addFieldToSelect($fieldName, $alias);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSelect()
    {
        return $this->sellerCollection->getSelect();
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        $this->sellerCollection->addFieldToFilter($attribute, $condition);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->sellerCollection->getSize();
    }

    /**
     * Retrieve an iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        $this->sellerCollection->setPageSize($this->batchSize);
        $lastPage = $this->sellerCollection->getLastPageNumber();
        $pageNumber = 1;
        do {
            $this->sellerCollection->clear();
            $this->sellerCollection->setCurPage($pageNumber);
            foreach ($this->sellerCollection->getItems() as $key => $value) {
                yield $key => $value;
            }
            $pageNumber++;
        } while ($pageNumber <= $lastPage);
    }

    /**
     * Joins Attribute
     *
     * @param string $alias alias for the joined attribute
     * @param string|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param string $bind attribute of the main entity to link with joined $filter
     * @param string|null $filter primary key for the joined entity (entity_id default)
     * @param string $joinType inner|left
     * @param int|null $storeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @see Collection::joinAttribute()
     */
    public function joinAttribute(
        string $alias,
        $attribute,
        string $bind,
        ?string $filter = null,
        string $joinType = 'inner',
        ?int $storeId = null
    ): void {
        $this->sellerCollection->joinAttribute($alias, $attribute, $bind, $filter, $joinType, $storeId);
    }
}
