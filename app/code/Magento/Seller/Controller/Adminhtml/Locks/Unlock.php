<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Locks;

use Magento\Seller\Model\AuthenticationInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Unlock Seller Controller
 */
class Unlock extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Seller::manage';

    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * Unlock constructor.
     *
     * @param Action\Context $context
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        Action\Context $context,
        AuthenticationInterface $authentication
    ) {
        parent::__construct($context);
        $this->authentication = $authentication;
    }

    /**
     * Unlock specified seller
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        try {
            // unlock seller
            if ($sellerId) {
                $this->authentication->unlock($sellerId);
                $this->getMessageManager()->addSuccessMessage(__('Seller has been unlocked successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            'seller/index/edit',
            ['id' => $sellerId]
        );
    }
}
