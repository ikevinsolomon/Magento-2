<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Seller log data logger.
 *
 * Saves and retrieves seller log data.
 */
class Logger
{
    /**
     * Resource instance.
     *
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Magento\Seller\Model\LogFactory
     */
    protected $logFactory;

    /**
     * @param ResourceConnection $resource
     * @param \Magento\Seller\Model\LogFactory $logFactory
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Seller\Model\LogFactory $logFactory
    ) {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
    }

    /**
     * Save (insert new or update existing) log.
     *
     * @param int $sellerId
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function log($sellerId, array $data)
    {
        $data = array_filter($data);

        if (!$data) {
            throw new \InvalidArgumentException("Log data is empty");
        }

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $connection->insertOnDuplicate(
            $this->resource->getTableName('seller_log'),
            array_merge(['seller_id' => $sellerId], $data),
            array_keys($data)
        );

        return $this;
    }

    /**
     * Load log by Seller Id.
     *
     * @param int $sellerId
     * @return Log
     */
    public function get($sellerId = null)
    {
        $data = (null !== $sellerId) ? $this->loadLogData($sellerId) : [];

        return $this->logFactory->create(
            [
                'sellerId' => isset($data['seller_id']) ? $data['seller_id'] : null,
                'lastLoginAt' => isset($data['last_login_at']) ? $data['last_login_at'] : null,
                'lastLogoutAt' => isset($data['last_logout_at']) ? $data['last_logout_at'] : null,
                'lastVisitAt' => isset($data['last_visit_at']) ? $data['last_visit_at'] : null
            ]
        );
    }

    /**
     * Load seller log data by seller id
     *
     * @param int $sellerId
     * @return array
     */
    protected function loadLogData($sellerId)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from(
                ['cl' => $this->resource->getTableName('seller_log')]
            )
            ->joinLeft(
                ['cv' => $this->resource->getTableName('seller_visitor')],
                'cv.seller_id = cl.seller_id',
                ['last_visit_at']
            )
            ->where(
                'cl.seller_id = ?',
                $sellerId
            )
            ->order(
                'cv.visitor_id DESC'
            )
            ->limit(1);

        return $connection->fetchRow($select);
    }
}
