<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Group;

use Magento\Seller\Api\Data\GroupInterfaceFactory;
use Magento\Seller\Api\GroupRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Controller class Save. Performs save action of sellers group
 */
class Save extends \Magento\Seller\Controller\Adminhtml\Group implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Seller\Api\Data\GroupExtensionInterfaceFactory
     */
    private $groupExtensionInterfaceFactory;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupDataFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Seller\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupDataFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Seller\Api\Data\GroupExtensionInterfaceFactory $groupExtensionInterfaceFactory
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->groupExtensionInterfaceFactory = $groupExtensionInterfaceFactory
            ?: ObjectManager::getInstance()->get(\Magento\Seller\Api\Data\GroupExtensionInterfaceFactory::class);
        parent::__construct(
            $context,
            $coreRegistry,
            $groupRepository,
            $groupDataFactory,
            $resultForwardFactory,
            $resultPageFactory
        );
    }

    /**
     * Store Seller Group Data to session
     *
     * @param array $sellerGroupData
     * @return void
     */
    protected function storeSellerGroupDataToSession($sellerGroupData)
    {
        if (array_key_exists('code', $sellerGroupData)) {
            $sellerGroupData['seller_group_code'] = $sellerGroupData['code'];
            unset($sellerGroupData['code']);
        }
        $this->_getSession()->setSellerGroupData($sellerGroupData);
    }

    /**
     * Create or save seller group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        /** @var \Magento\Seller\Api\Data\GroupInterface $sellerGroup */
        $sellerGroup = null;
        if ($taxClass) {
            $id = $this->getRequest()->getParam('id');
            $websitesToExclude = empty($this->getRequest()->getParam('seller_group_excluded_websites'))
                ? [] : $this->getRequest()->getParam('seller_group_excluded_websites');
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $sellerGroupCode = (string)$this->getRequest()->getParam('code');

                if ($id !== null) {
                    $sellerGroup = $this->groupRepository->getById((int)$id);
                    $sellerGroupCode = $sellerGroupCode ?: $sellerGroup->getCode();
                } else {
                    $sellerGroup = $this->groupDataFactory->create();
                }
                $sellerGroup->setCode(!empty($sellerGroupCode) ? $sellerGroupCode : null);
                $sellerGroup->setTaxClassId($taxClass);

                if ($websitesToExclude !== null) {
                    $sellerGroupExtensionAttributes = $this->groupExtensionInterfaceFactory->create();
                    $sellerGroupExtensionAttributes->setExcludeWebsiteIds($websitesToExclude);
                    $sellerGroup->setExtensionAttributes($sellerGroupExtensionAttributes);
                }

                $this->groupRepository->save($sellerGroup);

                $this->messageManager->addSuccessMessage(__('You saved the seller group.'));
                $resultRedirect->setPath('seller/group');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($sellerGroup != null) {
                    $this->storeSellerGroupDataToSession(
                        $this->dataObjectProcessor->buildOutputDataArray(
                            $sellerGroup,
                            \Magento\Seller\Api\Data\GroupInterface::class
                        )
                    );
                }
                $resultRedirect->setPath('seller/group/edit', ['id' => $id]);
            }
            return $resultRedirect;
        } else {
            return $this->resultForwardFactory->create()->forward('new');
        }
    }
}
