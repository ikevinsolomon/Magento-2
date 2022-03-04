<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Api;

/**
 * Interface for managing seller attributes metadata.
 * @api
 * @since 100.0.2
 */
interface SellerMetadataManagementInterface extends MetadataManagementInterface
{
    const ENTITY_TYPE_SELLER = 'seller';
}
