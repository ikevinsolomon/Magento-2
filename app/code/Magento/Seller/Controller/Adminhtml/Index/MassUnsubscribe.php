<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Model\Config\Share;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class to mass unsubscribe sellers by ids
 */
class MassUnsubscribe extends AbstractMassAction implements HttpPostActionInterface
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param StoreManagerInterface $storeManager
     * @param Share $shareConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SellerRepositoryInterface $sellerRepository,
        SubscriptionManagerInterface $subscriptionManager,
        StoreManagerInterface $storeManager,
        Share $shareConfig
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->sellerRepository = $sellerRepository;
        $this->subscriptionManager = $subscriptionManager;
        $this->storeManager = $storeManager;
        $this->shareConfig = $shareConfig;
    }

    /**
     * Seller mass unsubscribe action
     *
     * @param AbstractCollection $collection
     * @return Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $sellersUpdated = 0;
        foreach ($collection->getAllIds() as $sellerId) {
            // Verify that seller exists
            $seller = $this->sellerRepository->getById($sellerId);
            foreach ($this->getUnsubscribeStoreIds($seller) as $storeId) {
                $this->subscriptionManager->unsubscribeSeller((int)$sellerId, $storeId);
            }
            $sellersUpdated++;
        }

        if ($sellersUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $sellersUpdated));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * Get store ids to unsubscribe seller
     *
     * @param SellerInterface $seller
     * @return array
     */
    private function getUnsubscribeStoreIds(SellerInterface $seller): array
    {
        $storeIds = [];
        if ($this->shareConfig->isGlobalScope()) {
            foreach ($this->storeManager->getStores() as $store) {
                $storeIds[(int)$store->getWebsiteId()] = (int)$store->getId();
            }
        } else {
            $storeIds = [(int)$seller->getStoreId()];
        }

        return $storeIds;
    }
}
