<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Seller\Model\ResourceModel\Address\CollectionFactory;
use Magento\Seller\Api\AddressRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class to delete selected seller addresses through massaction
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see MassDelete::_isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Delete specified seller addresses using grid massaction
     *
     * @return Json
     * @throws LocalizedException
     */
    public function execute(): Json
    {
        $sellerData = $this->_session->getData('seller_data');
        /** @var \Magento\Seller\Model\ResourceModel\Address\Collection $collection */
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $error = false;

        try {
            if ($sellerData && $sellerData['seller_id']) {
                $collection->addFieldToFilter('parent_id', $sellerData['seller_id']);
            } else {
                throw new \Exception();
            }
            $collectionSize = $collection->getSize();
            /** @var \Magento\Seller\Model\Address $address */
            foreach ($collection as $address) {
                $this->addressRepository->deleteById($address->getId());
            }
            $message = __('A total of %1 record(s) have been deleted.', $collectionSize);
        } catch (NoSuchEntityException $e) {
            $message = __('There is no such address entity to delete.');
            $error = true;
            $this->logger->critical($e);
        } catch (LocalizedException $e) {
            $message = __($e->getMessage());
            $error = true;
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $message = __('We can\'t mass delete the addresses right now.');
            $error = true;
            $this->logger->critical($e);
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
