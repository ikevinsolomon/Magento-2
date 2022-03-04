<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Group;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Seller\Controller\RegistryConstants;

class NewAction extends \Magento\Seller\Controller\Adminhtml\Group implements HttpGetActionInterface
{
    /**
     * Initialize current group and set it in the registry.
     *
     * @return int
     */
    protected function _initGroup()
    {
        $groupId = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_GROUP_ID, $groupId);

        return $groupId;
    }

    /**
     * Edit or create seller group.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $groupId = $this->_initGroup();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Seller::seller_group');
        $resultPage->getConfig()->getTitle()->prepend(__('Seller Groups'));
        $resultPage->addBreadcrumb(__('Sellers'), __('Sellers'));
        $resultPage->addBreadcrumb(__('Seller Groups'), __('Seller Groups'), $this->getUrl('seller/group'));

        if ($groupId === null) {
            $resultPage->addBreadcrumb(__('New Group'), __('New Seller Groups'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Seller Group'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Group'), __('Edit Seller Groups'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->groupRepository->getById($groupId)->getCode()
            );
        }

        $resultPage->getLayout()->addBlock(\Magento\Seller\Block\Adminhtml\Group\Edit::class, 'group', 'content')
            ->setEditMode((bool)$groupId);

        return $resultPage;
    }
}
