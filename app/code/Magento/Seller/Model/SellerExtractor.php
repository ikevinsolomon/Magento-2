<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model;

use Magento\Seller\Api\SellerMetadataInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\GroupManagementInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Seller Extractor model.
 */
class SellerExtractor
{
    /**
     * @var \Magento\Seller\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Seller\Api\Data\SellerInterfaceFactory
     */
    protected $sellerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $sellerGroupManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param Metadata\FormFactory $formFactory
     * @param \Magento\Seller\Api\Data\SellerInterfaceFactory $sellerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $sellerGroupManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Seller\Model\Metadata\FormFactory $formFactory,
        \Magento\Seller\Api\Data\SellerInterfaceFactory $sellerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $sellerGroupManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->formFactory = $formFactory;
        $this->sellerFactory = $sellerFactory;
        $this->storeManager = $storeManager;
        $this->sellerGroupManagement = $sellerGroupManagement;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Creates a Seller object populated with the given form code and request data.
     *
     * @param string $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     * @return SellerInterface
     */
    public function extract(
        $formCode,
        RequestInterface $request,
        array $attributeValues = []
    ) {
        $sellerForm = $this->formFactory->create(
            SellerMetadataInterface::ENTITY_TYPE_SELLER,
            $formCode,
            $attributeValues
        );

        $sellerData = $sellerForm->extractData($request);
        $sellerData = $sellerForm->compactData($sellerData);

        $allowedAttributes = $sellerForm->getAllowedAttributes();
        $isGroupIdEmpty = !isset($allowedAttributes['group_id']);

        $sellerDataObject = $this->sellerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $sellerDataObject,
            $sellerData,
            \Magento\Seller\Api\Data\SellerInterface::class
        );

        $store = $this->storeManager->getStore();
        $storeId = $store->getId();

        if ($isGroupIdEmpty) {
            $groupId = isset($sellerData['group_id']) ? $sellerData['group_id']
                : $this->sellerGroupManagement->getDefaultGroup($storeId)->getId();
            $sellerDataObject->setGroupId(
                $groupId
            );
        }

        $sellerDataObject->setWebsiteId($store->getWebsiteId());
        $sellerDataObject->setStoreId($storeId);

        return $sellerDataObject;
    }
}
