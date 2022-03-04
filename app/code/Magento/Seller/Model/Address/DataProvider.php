<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Address;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\ResourceModel\Address\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Seller\Model\Address;
use Magento\Seller\Model\FileUploaderDataResolver;
use Magento\Seller\Model\AttributeMetadataResolver;
use Magento\Ui\Component\Form\Element\Multiline;

/**
 * Dataprovider of seller addresses for seller address grid.
 *
 * @property \Magento\Seller\Model\ResourceModel\Address\Collection $collection
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /*
     * @var ContextInterface
     */
    private $context;

    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];

    /**
     * @var array
     */
    private static $attributesToEliminate = [
        'region',
        'vat_is_valid',
        'vat_request_date',
        'vat_request_id',
        'vat_request_success'
    ];

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver
     */
    private $attributeMetadataResolver;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $addressCollectionFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param Config $eavConfig
     * @param ContextInterface $context
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param AttributeMetadataResolver $attributeMetadataResolver
     * @param array $meta
     * @param array $data
     * @param bool $allowToShowHiddenAttributes
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $addressCollectionFactory,
        SellerRepositoryInterface $sellerRepository,
        Config $eavConfig,
        ContextInterface $context,
        FileUploaderDataResolver $fileUploaderDataResolver,
        AttributeMetadataResolver $attributeMetadataResolver,
        array $meta = [],
        array $data = [],
        $allowToShowHiddenAttributes = true
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $addressCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->sellerRepository = $sellerRepository;
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->context = $context;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->attributeMetadataResolver = $attributeMetadataResolver;
        $this->meta['general']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('seller_address')
        );
    }

    /**
     * Get Addresses data and process seller default billing & shipping addresses
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData(): array
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Address $item */
        foreach ($items as $item) {
            $addressId = $item->getEntityId();
            $item->load($addressId);
            $this->loadedData[$addressId] = $item->getData();
            $sellerId = $this->loadedData[$addressId]['parent_id'];
            /** @var \Magento\Seller\Model\Seller $seller */
            $seller = $this->sellerRepository->getById($sellerId);
            $defaultBilling = $seller->getDefaultBilling();
            $defaultShipping = $seller->getDefaultShipping();
            $this->prepareAddressData($addressId, $this->loadedData, $defaultBilling, $defaultShipping);
            $this->fileUploaderDataResolver->overrideFileUploaderData($item, $this->loadedData[$addressId]);
        }

        if (null === $this->loadedData) {
            $this->loadedData[''] = $this->getDefaultData();
        }

        return $this->loadedData;
    }

    /**
     * Prepare address data
     *
     * @param int $addressId
     * @param array $addresses
     * @param string|null $defaultBilling
     * @param string|null $defaultShipping
     * @return void
     */
    private function prepareAddressData($addressId, array &$addresses, $defaultBilling, $defaultShipping): void
    {
        if (null !== $defaultBilling && $addressId === $defaultBilling) {
            $addresses[$addressId]['default_billing'] = '1';
        }
        if (null !== $defaultShipping && $addressId === $defaultShipping) {
            $addresses[$addressId]['default_shipping'] = '1';
        }
        foreach ($this->meta['general']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($this->loadedData[$addressId][$attributeName])
                && !\is_array($this->loadedData[$addressId][$attributeName])
            ) {
                $this->loadedData[$addressId][$attributeName] = explode(
                    "\n",
                    $this->loadedData[$addressId][$attributeName]
                );
            }
        }
    }

    /**
     * Get default seller data for adding new address
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return array
     */
    private function getDefaultData(): array
    {
        $parentId = $this->context->getRequestParam('parent_id');
        $seller = $this->sellerRepository->getById($parentId);
        $data = [
            'parent_id' => $parentId,
            'firstname' => $seller->getFirstname(),
            'lastname' => $seller->getLastname()
        ];

        return $data;
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (\in_array($attribute->getFrontendInput(), $this->bannedInputTypes, true)) {
                continue;
            }
            if (\in_array($attribute->getAttributeCode(), self::$attributesToEliminate, true)) {
                continue;
            }

            $meta[$attribute->getAttributeCode()] = $this->attributeMetadataResolver->getAttributesMeta(
                $attribute,
                $entityType,
                $this->allowToShowHiddenAttributes
            );
        }
        $this->attributeMetadataResolver->processWebsiteMeta($meta);

        return $meta;
    }
}
