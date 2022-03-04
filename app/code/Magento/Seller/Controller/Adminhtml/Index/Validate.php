<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Message\Error;
use Magento\Seller\Controller\Adminhtml\Index as SellerAction;

/**
 * Class for validation of seller
 */
class Validate extends SellerAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * Seller validation
     *
     * @param \Magento\Framework\DataObject $response
     * @return SellerInterface|null
     */
    protected function _validateSeller($response)
    {
        $seller = null;
        $errors = [];

        try {
            /** @var SellerInterface $seller */
            $seller = $this->sellerDataFactory->create();

            $sellerForm = $this->_formFactory->create(
                'seller',
                'adminhtml_seller',
                [],
                true
            );
            $sellerForm->setInvisibleIgnored(true);

            $data = $sellerForm->extractData($this->getRequest(), 'seller');

            if ($seller->getWebsiteId()) {
                unset($data['website_id']);
            }

            $this->dataObjectHelper->populateWithArray(
                $seller,
                $data,
                \Magento\Seller\Api\Data\SellerInterface::class
            );
            $submittedData = $this->getRequest()->getParam('seller');
            if (isset($submittedData['entity_id'])) {
                $entity_id = $submittedData['entity_id'];
                $seller->setId($entity_id);
            }
            $errors = $this->sellerAccountManagement->validate($seller)->getMessages();
        } catch (\Magento\Framework\Validator\Exception $exception) {
            /* @var $error Error */
            foreach ($exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR) as $error) {
                $errors[] = $error->getText();
            }
        }

        if ($errors) {
            $messages = $response->hasMessages() ? $response->getMessages() : [];
            foreach ($errors as $error) {
                $messages[] = $error;
            }
            $response->setMessages($messages);
            $response->setError(1);
        }

        return $seller;
    }

    /**
     * AJAX seller validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);

        $this->_validateSeller($response);
        $resultJson = $this->resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
