<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\File\Seller;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Model\FileUploader;
use Magento\Seller\Model\FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var FileUploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var SellerMetadataInterface
     */
    private $sellerMetadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param FileUploaderFactory $fileUploaderFactory
     * @param SellerMetadataInterface $sellerMetadataService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $fileUploaderFactory,
        SellerMetadataInterface $sellerMetadataService,
        LoggerInterface $logger
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->sellerMetadataService = $sellerMetadataService;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            if (empty($_FILES)) {
                throw new \Exception('$_FILES array is empty.');
            }

            $attributeCode = key($_FILES['seller']['name']);
            $attributeMetadata = $this->sellerMetadataService->getAttributeMetadata($attributeCode);

            /** @var FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create([
                'attributeMetadata' => $attributeMetadata,
                'entityTypeCode' => SellerMetadataInterface::ENTITY_TYPE_SELLER,
                'scope' => 'seller',
            ]);

            $errors = $fileUploader->validate();
            if (true !== $errors) {
                $errorMessage = implode('</br>', $errors);
                throw new LocalizedException(__($errorMessage));
            }

            $result = $fileUploader->upload();
        } catch (LocalizedException $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => __('Something went wrong while saving file.'),
                'errorcode' => $e->getCode(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
