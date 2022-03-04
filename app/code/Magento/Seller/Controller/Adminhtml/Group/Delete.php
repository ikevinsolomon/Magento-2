<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Group;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 */
class Delete extends \Magento\Seller\Controller\Adminhtml\Group implements HttpPostActionInterface
{
    /**
     * Delete seller group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->groupRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the seller group.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The seller group no longer exists.'));
                return $resultRedirect->setPath('seller/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('seller/group/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('seller/group');
    }
}
