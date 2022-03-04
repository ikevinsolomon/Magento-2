<?php
declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Seller\Api\AddressRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Button for deletion of seller address in admin
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param AddressRepositoryInterface $addressRepository
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        AddressRepositoryInterface $addressRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
    }

    /**
     * Delete seller address action
     *
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): Json
    {
        $sellerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('id', false);
        $error = false;
        $message = '';
        if ($addressId && $this->addressRepository->getById($addressId)->getSellerId() === $sellerId) {
            try {
                $this->addressRepository->deleteById($addressId);
                $message = __('You deleted the address.');
            } catch (\Exception $e) {
                $error = true;
                $message = __('We can\'t delete the address right now.');
                $this->logger->critical($e);
            }
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
