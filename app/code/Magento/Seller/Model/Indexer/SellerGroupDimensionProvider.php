<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\Indexer;

use Magento\Seller\Model\ResourceModel\Group\CollectionFactory as SellerGroupCollectionFactory;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\DimensionProviderInterface;

/**
 * Class SellerGroupDimensionProvider
 */
class SellerGroupDimensionProvider implements DimensionProviderInterface
{
    /**
     * Name for seller group dimension for multidimensional indexer
     * 'cg' - stands for 'seller_group'
     */
    const DIMENSION_NAME = 'cg';

    /**
     * @var SellerGroupCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \SplFixedArray
     */
    private $sellerGroupsDataIterator;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @param SellerGroupCollectionFactory $collectionFactory
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(SellerGroupCollectionFactory $collectionFactory, DimensionFactory $dimensionFactory)
    {
        $this->dimensionFactory = $dimensionFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->getSellerGroups() as $sellerGroup) {
            yield $this->dimensionFactory->create(self::DIMENSION_NAME, (string)$sellerGroup);
        }
    }

    /**
     * Get Seller Groups
     *
     * @return array
     */
    private function getSellerGroups(): array
    {
        if ($this->sellerGroupsDataIterator === null) {
            $sellerGroups = $this->collectionFactory->create()->getAllIds();
            $this->sellerGroupsDataIterator = is_array($sellerGroups) ? $sellerGroups : [];
        }

        return $this->sellerGroupsDataIterator;
    }
}
