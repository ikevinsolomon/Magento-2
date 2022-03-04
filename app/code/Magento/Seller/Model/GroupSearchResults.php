<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model;

use Magento\Seller\Api\Data\GroupSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Seller Groups search results.
 */
class GroupSearchResults extends SearchResults implements GroupSearchResultsInterface
{
}
