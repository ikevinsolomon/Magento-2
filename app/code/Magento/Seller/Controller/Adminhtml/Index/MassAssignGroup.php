<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Seller\Model\Seller;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Seller\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassAssignGroup
 */
class MassAssignGroup extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SellerRepositoryInterface $sellerRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->sellerRepository = $sellerRepository;
    }

    /**
     * Seller mass assign group action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $sellersUpdated = 0;
        foreach ($collection->getAllIds() as $sellerId) {
            // Verify seller exists
            $seller = $this->sellerRepository->getById($sellerId);
            $seller->setGroupId($this->getRequest()->getParam('group'));
            // No need to validate seller and seller address during assigning seller to the group
            $this->setIgnoreValidationFlag($seller);
            $this->sellerRepository->save($seller);
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

    /**
     * Set ignore_validation_flag to skip unnecessary address and seller validation
     *
     * @param Seller $seller
     * @return void
     */
    private function setIgnoreValidationFlag($seller)
    {
        $seller->setData('ignore_validation_flag', true);
    }
}
