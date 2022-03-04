<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Helper;

use Magento\Seller\Api\SellerNameGenerationInterface;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;

/**
 * Seller helper for view.
 */
class View extends \Magento\Framework\App\Helper\AbstractHelper implements SellerNameGenerationInterface
{
    /**
     * @var SellerMetadataInterface
     */
    protected $_sellerMetadataService;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SellerMetadataInterface $sellerMetadataService
     * @param Escaper|null $escaper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SellerMetadataInterface $sellerMetadataService,
        Escaper $escaper = null
    ) {
        $this->_sellerMetadataService = $sellerMetadataService;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function getSellerName(SellerInterface $sellerData)
    {
        $name = '';
        $prefixMetadata = $this->_sellerMetadataService->getAttributeMetadata('prefix');
        if ($prefixMetadata->isVisible() && $sellerData->getPrefix()) {
            $name .= $sellerData->getPrefix() . ' ';
        }

        $name .= $sellerData->getFirstname();

        $middleNameMetadata = $this->_sellerMetadataService->getAttributeMetadata('middlename');
        if ($middleNameMetadata->isVisible() && $sellerData->getMiddlename()) {
            $name .= ' ' . $sellerData->getMiddlename();
        }

        $name .= ' ' . $sellerData->getLastname();

        $suffixMetadata = $this->_sellerMetadataService->getAttributeMetadata('suffix');
        if ($suffixMetadata->isVisible() && $sellerData->getSuffix()) {
            $name .= ' ' . $sellerData->getSuffix();
        }

        return $this->escaper->escapeHtml($name);
    }
}
