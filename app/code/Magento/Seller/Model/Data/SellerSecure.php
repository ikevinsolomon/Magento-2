<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model\Data;

/**
 * Class containing secure seller data that cannot be exposed as part of \Magento\Seller\Api\Data\SellerInterface
 *
 * @method string getRpToken()
 * @method string getRpTokenCreatedAt()
 * @method string getPasswordHash()
 * @method string getDeleteable()
 * @method setRpToken(string $rpToken)
 * @method setRpTokenCreatedAt(string $rpTokenCreatedAt)
 * @method setPasswordHash(string $hashedPassword)
 * @method setDeleteable(bool $deleteable)
 */
class SellerSecure extends \Magento\Framework\DataObject
{
}
