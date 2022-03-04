<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Seller\Api\AccountManagementInterface;
use Magento\Seller\Api\AddressRepositoryInterface;
use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\AddressInterfaceFactory;
use Magento\Seller\Api\Data\SellerInterfaceFactory;
use Magento\Seller\Model\Address\Mapper;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObjectFactory;

/**
 * Class Viewfile serves to show file or image by file/image name provided in request parameters.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Viewfile extends \Magento\Seller\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Seller\Model\SellerFactory $sellerFactory
     * @param \Magento\Seller\Model\AddressFactory $addressFactory
     * @param \Magento\Seller\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Seller\Helper\View $viewHelper
     * @param \Magento\Framework\Math\Random $random
     * @param SellerRepositoryInterface $sellerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $sellerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param SellerInterfaceFactory $sellerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Seller\Model\Seller\Mapper $sellerMapper
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Seller\Model\SellerFactory $sellerFactory,
        \Magento\Seller\Model\AddressFactory $addressFactory,
        \Magento\Seller\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Seller\Helper\View $viewHelper,
        \Magento\Framework\Math\Random $random,
        SellerRepositoryInterface $sellerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $sellerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        SellerInterfaceFactory $sellerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        \Magento\Seller\Model\Seller\Mapper $sellerMapper,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        DataObjectFactory $objectFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $sellerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $sellerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $sellerAccountManagement,
            $addressRepository,
            $sellerDataFactory,
            $addressDataFactory,
            $sellerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
        $this->resultRawFactory = $resultRawFactory;
        $this->urlDecoder  = $urlDecoder;
    }

    /**
     * Seller view file action
     *
     * @return ResultInterface|ResponseInterface|void
     * @throws NotFoundException
     */
    public function execute()
    {
        list($file, $plain) = $this->getFileParams();

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = SellerMetadataInterface::ENTITY_TYPE_SELLER . '/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        if (mb_strpos($path, '..') !== false || (!$directory->isFile($fileName)
            && !$this->_objectManager->get(\Magento\MediaStorage\Helper\File\Storage::class)->processStorageFile($path))
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        if ($plain) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));
            return $resultRaw;
        } else {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $name = pathinfo($path, PATHINFO_BASENAME);
            return $this->_fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }

    /**
     * Get parameters from request.
     *
     * @return array
     * @throws NotFoundException
     */
    private function getFileParams()
    {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->urlDecoder->decode(
                $this->getRequest()->getParam('image')
            );
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        return [$file, $plain];
    }
}
