<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Logout controller
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 */
class Logout extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Seller\Model\Session
     */
    protected $sellerSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Initialize Logout controller
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Seller\Model\Session $sellerSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Seller\Model\Session $sellerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->sellerSession = $sellerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Seller logout action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $lastSellerId = $this->sellerSession->getId();
        $this->sellerSession->logout()
            ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastSellerId($lastSellerId);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['message' => 'Logout Successful']);
    }
}
