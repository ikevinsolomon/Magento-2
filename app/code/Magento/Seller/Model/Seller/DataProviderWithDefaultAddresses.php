<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Seller;

use Magento\Seller\Model\Address;
use Magento\Seller\Model\Seller;
use Magento\Seller\Model\SellerFactory;
use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Seller\Model\FileUploaderDataResolver;
use Magento\Seller\Model\AttributeMetadataResolver;
use Magento\Ui\Component\Form\Element\Multiline;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Refactored version of Magento\Seller\Model\Seller\DataProvider with eliminated usage of addresses collection.
 */
class DataProviderWithDefaultAddresses extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData = [];

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * Seller fields that must be removed
     *
     * @var array
     */
    private static $forbiddenSellerFields = [
        'password_hash',
        'rp_token',
    ];

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver
     */
    private $attributeMetadataResolver;

    /**
     * @var SellerFactory
     */
    private $sellerFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param SellerCollectionFactory $sellerCollectionFactory
     * @param Config $eavConfig
     * @param CountryFactory $countryFactory
     * @param SessionManagerInterface $session
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param AttributeMetadataResolver $attributeMetadataResolver
     * @param bool $allowToShowHiddenAttributes
     * @param array $meta
     * @param array $data
     * @param SellerFactory $sellerFactory
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        SellerCollectionFactory $sellerCollectionFactory,
        Config $eavConfig,
        CountryFactory $countryFactory,
        SessionManagerInterface $session,
        FileUploaderDataResolver $fileUploaderDataResolver,
        AttributeMetadataResolver $attributeMetadataResolver,
        $allowToShowHiddenAttributes = true,
        array $meta = [],
        array $data = [],
        SellerFactory $sellerFactory = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $sellerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->session = $session;
        $this->countryFactory = $countryFactory;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->attributeMetadataResolver = $attributeMetadataResolver;
        $this->meta['seller']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('seller')
        );
        $this->sellerFactory = $sellerFactory ?: ObjectManager::getInstance()->get(SellerFactory::class);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Seller $seller */
        foreach ($items as $seller) {
            $result['seller'] = $seller->getData();

            $this->fileUploaderDataResolver->overrideFileUploaderData($seller, $result['seller']);

            $result['seller'] = array_diff_key(
                $result['seller'],
                array_flip(self::$forbiddenSellerFields)
            );
            $this->prepareCustomAttributeValue($result['seller']);
            unset($result['address']);

            $result['default_billing_address'] = $this->prepareDefaultAddress(
                $seller->getDefaultBillingAddress()
            );
            $result['default_shipping_address'] = $this->prepareDefaultAddress(
                $seller->getDefaultShippingAddress()
            );
            $result['seller_id'] = $seller->getId();

            $this->loadedData[$seller->getId()] = $result;
        }
        $data = $this->session->getSellerFormData();
        if (!empty($data)) {
            $seller = $this->sellerFactory->create();
            $this->fileUploaderDataResolver->overrideFileUploaderData($seller, $data['seller']);
            $sellerId = $data['seller']['entity_id'] ?? null;
            $this->loadedData[$sellerId] = $data;
            $this->session->unsSellerFormData();
        }

        return $this->loadedData;
    }

    /**
     * Prepare default address data.
     *
     * @param Address|false $address
     * @return array
     */
    private function prepareDefaultAddress($address): array
    {
        if (!$address) {
            return [];
        }

        $addressData = $address->getData();
        if (isset($addressData['street']) && !is_array($addressData['street'])) {
            $addressData['street'] = explode("\n", $addressData['street']);
        }
        if (!empty($addressData['country_id'])) {
            $addressData['country'] = $this->countryFactory->create()
                ->loadByCode($addressData['country_id'])
                ->getName();
        }
        $addressData['region'] = $address->getRegion();

        return $addressData;
    }

    /***
     * Prepare values for Custom Attributes.
     *
     * @param array $data
     * @return void
     */
    private function prepareCustomAttributeValue(array &$data): void
    {
        foreach ($this->meta['seller']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($data[$attributeName])
                && !is_array($data[$attributeName])
            ) {
                $data[$attributeName] = explode("\n", $data[$attributeName]);
            }
        }
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws LocalizedException
     */
    private function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
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
