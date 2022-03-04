<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api;

/**
 * Interface for retrieval information about seller attributes metadata.
 * @api
 * @since 100.0.2
 */
interface SellerMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_SELLER = 9;

    const ENTITY_TYPE_SELLER = 'seller';

    const DATA_INTERFACE_NAME = \Magento\Seller\Api\Data\SellerInterface::class;
}
