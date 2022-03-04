<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class to mass subscribe sellers by ids
 */
class MassSubscribe extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param SubscriptionManagerInterface $subscriptionManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SellerRepositoryInterface $sellerRepository,
        SubscriptionManagerInterface $subscriptionManager
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->sellerRepository = $sellerRepository;
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * Seller mass subscribe action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $sellersUpdated = 0;
        foreach ($collection->getAllIds() as $sellerId) {
            $seller = $this->sellerRepository->getById($sellerId);
            $storeId = (int)$seller->getStoreId();
            $this->subscriptionManager->subscribeSeller($sellerId, $storeId);
            $sellersUpdated++;
        }

        if ($sellersUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $sellersUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
