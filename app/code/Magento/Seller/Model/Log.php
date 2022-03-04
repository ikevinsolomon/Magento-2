<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

/**
 * Seller log model.
 *
 * Contains seller log data.
 */
class Log
{
    /**
     * Seller ID.
     *
     * @var int
     */
    protected $sellerId;

    /**
     * Date and time of seller's last login.
     *
     * @var string
     */
    protected $lastLoginAt;

    /**
     * Date and time of seller's last logout.
     *
     * @var string
     */
    protected $lastVisitAt;

    /**
     * Date and time of seller's last visit.
     *
     * @var string
     */
    protected $lastLogoutAt;

    /**
     * @param int $sellerId
     * @param string $lastLoginAt
     * @param string $lastVisitAt
     * @param string $lastLogoutAt
     */
    public function __construct($sellerId = null, $lastLoginAt = null, $lastVisitAt = null, $lastLogoutAt = null)
    {
        $this->sellerId = $sellerId;
        $this->lastLoginAt = $lastLoginAt;
        $this->lastVisitAt = $lastVisitAt;
        $this->lastLogoutAt = $lastLogoutAt;
    }

    /**
     * Retrieve seller id
     *
     * @return int
     */
    public function getSellerId()
    {
        return $this->sellerId;
    }

    /**
     * Retrieve last login date as string
     *
     * @return string
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * Retrieve last visit date as string
     *
     * @return string
     */
    public function getLastVisitAt()
    {
        return $this->lastVisitAt;
    }

    /**
     * Retrieve last logout date as string
     *
     * @return string
     */
    public function getLastLogoutAt()
    {
        return $this->lastLogoutAt;
    }
}
