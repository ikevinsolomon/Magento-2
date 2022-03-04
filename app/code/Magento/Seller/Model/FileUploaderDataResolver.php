<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Seller\Api\AddressMetadataInterface;
use Magento\Seller\Api\SellerMetadataInterface;

/**
 * Class to retrieve file uploader data for seller and seller address file & image attributes
 */
class FileUploaderDataResolver
{
    /**
     * Maximum file size allowed for file_uploader UI component
     * This constant was copied from deprecated data provider \Magento\Seller\Model\Seller\DataProvider
     */
    private const MAX_FILE_SIZE = 2097152;

    /**
     * @var FileProcessorFactory
     */
    private $fileProcessorFactory;

    /**
     * File types allowed for file_uploader UI component
     *
     * @var array
     */
    private $fileUploaderTypes = [
        'image',
        'file',
    ];

    /**
     * @param FileProcessorFactory $fileProcessorFactory
     */
    public function __construct(
        FileProcessorFactory $fileProcessorFactory
    ) {
        $this->fileProcessorFactory = $fileProcessorFactory;
    }

    /**
     * Override file uploader UI component data
     *
     * Overrides data for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Seller|Address $entity
     * @param array $entityData
     * @return void
     */
    public function overrideFileUploaderData($entity, array &$entityData): void
    {
        $attributes = $entity->getAttributes();
        foreach ($attributes as $attribute) {
            /** @var Attribute $attribute */
            if (\in_array($attribute->getFrontendInput(), $this->fileUploaderTypes, true)) {
                $entityData[$attribute->getAttributeCode()] = $this->getFileUploaderData(
                    $entity->getEntityType(),
                    $attribute,
                    $entityData
                );
            }
        }
    }

    /**
     * Retrieve array of values required by file uploader UI component
     *
     * @param Type $entityType
     * @param Attribute $attribute
     * @param array $sellerData
     * @return array
     */
    private function getFileUploaderData(
        Type $entityType,
        Attribute $attribute,
        array $sellerData
    ): array {
        $attributeCode = $attribute->getAttributeCode();

        $file = $sellerData[$attributeCode] ?? null;

        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->fileProcessorFactory->create(['entityTypeCode' => $entityType->getEntityTypeCode()]);

        if (!empty($file)
            && $fileProcessor->isExist($file)
        ) {
            $stat = $fileProcessor->getStat($file);
            $viewUrl = $fileProcessor->getViewUrl($file, $attribute->getFrontendInput());

            return [
                [
                    'file' => $file,
                    'size' => null !== $stat ? $stat['size'] : 0,
                    'url' => $viewUrl ?? '',
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    'name' => basename($file),
                    'type' => $fileProcessor->getMimeType($file),
                ],
            ];
        }

        return [];
    }

    /**
     * Override file uploader UI component metadata
     *
     * Overrides metadata for attributes with frontend_input equal to 'image' or 'file'.
     *
     * @param Type $entityType
     * @param AbstractAttribute $attribute
     * @param array $config
     * @return void
     */
    public function overrideFileUploaderMetadata(
        Type $entityType,
        AbstractAttribute $attribute,
        array &$config
    ): void {
        if (\in_array($attribute->getFrontendInput(), $this->fileUploaderTypes, true)) {
            $maxFileSize = self::MAX_FILE_SIZE;

            if (isset($config['validation']['max_file_size'])) {
                $maxFileSize = (int)$config['validation']['max_file_size'];
            }

            $allowedExtensions = [];

            if (isset($config['validation']['file_extensions'])) {
                $allowedExtensions = explode(',', $config['validation']['file_extensions']);
                array_walk(
                    $allowedExtensions,
                    function (&$value) {
                        $value = strtolower(trim($value));
                    }
                );
            }

            $allowedExtensions = implode(' ', $allowedExtensions);

            $entityTypeCode = $entityType->getEntityTypeCode();
            $url = $this->getFileUploadUrl($entityTypeCode);

            $config = [
                'dataType' => $this->getMetadataValue($config, 'dataType'),
                'formElement' => 'fileUploader',
                'componentType' => 'fileUploader',
                'maxFileSize' => $maxFileSize,
                'allowedExtensions' => $allowedExtensions,
                'uploaderConfig' => [
                    'url' => $url,
                ],
                'label' => $this->getMetadataValue($config, 'label'),
                'sortOrder' => $this->getMetadataValue($config, 'sortOrder'),
                'required' => $this->getMetadataValue($config, 'required'),
                'visible' => $this->getMetadataValue($config, 'visible'),
                'validation' => $this->getMetadataValue($config, 'validation'),
            ];
        }
    }

    /**
     * Retrieve metadata value
     *
     * @param array $config
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    private function getMetadataValue($config, $name, $default = null)
    {
        return $config[$name] ?? $default;
    }

    /**
     * Retrieve URL to file upload
     *
     * @param string $entityTypeCode
     * @return string
     */
    private function getFileUploadUrl($entityTypeCode): string
    {
        switch ($entityTypeCode) {
            case SellerMetadataInterface::ENTITY_TYPE_SELLER:
                $url = 'seller/file/seller_upload';
                break;

            case AddressMetadataInterface::ENTITY_TYPE_ADDRESS:
                $url = 'seller/file/address_upload';
                break;

            default:
                $url = '';
                break;
        }
        return $url;
    }
}
