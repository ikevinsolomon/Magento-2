<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Api;

/**
 * Interface for retrieval information about seller address attributes metadata.
 * @api
 * @since 100.0.2
 */
interface AddressMetadataInterface extends MetadataInterface
{
    const ATTRIBUTE_SET_ID_ADDRESS = 10;

    const ENTITY_TYPE_ADDRESS = 'seller_address';

    const DATA_INTERFACE_NAME = \Magento\Seller\Api\Data\AddressInterface::class;
}
