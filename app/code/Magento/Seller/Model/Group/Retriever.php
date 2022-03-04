<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Group;

use Magento\Seller\Model\Session;

/**
 * Class for getting current seller group from seller session.
 */
class Retriever implements RetrieverInterface
{
    /**
     * @var Session
     */
    private $sellerSession;

    /**
     * @param Session $sellerSession
     */
    public function __construct(Session $sellerSession)
    {
        $this->sellerSession = $sellerSession;
    }

    /**
     * @inheritdoc
     */
    public function getSellerGroupId()
    {
        return $this->sellerSession->getSellerGroupId();
    }
}
