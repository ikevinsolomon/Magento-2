<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\ResourceModel\Online\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Seller\Model\Visitor;
use Magento\Framework\Api;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * Flat seller online grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends SearchResult
{
    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var Visitor
     */
    protected $visitorModel;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param Visitor $visitorModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel,
        Visitor $visitorModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->date = $date;
        $this->visitorModel = $visitorModel;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $connection = $this->getConnection();
        $lastDate = $this->date->gmtTimestamp() - $this->visitorModel->getOnlineInterval() * self::SECONDS_IN_MINUTE;
        $this->getSelect()->joinLeft(
            ['seller' => $this->getTable('seller_entity')],
            'seller.entity_id = main_table.seller_id',
            ['email', 'firstname', 'lastname']
        )->where(
            'main_table.last_visit_at >= ?',
            $connection->formatDate($lastDate)
        );
        $expression = $connection->getCheckSql(
            'main_table.seller_id IS NOT NULL AND main_table.seller_id != 0',
            $connection->quote(Visitor::VISITOR_TYPE_SELLER),
            $connection->quote(Visitor::VISITOR_TYPE_VISITOR)
        );
        $this->getSelect()->columns(['visitor_type' => $expression]);
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'visitor_type') {
            $field = 'seller_id';
            if (is_array($condition) && isset($condition['eq'])) {
                $condition = $condition['eq'] == Visitor::VISITOR_TYPE_SELLER ? ['gt' => 0] : ['null' => true];
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
