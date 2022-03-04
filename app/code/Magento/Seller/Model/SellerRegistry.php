<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Seller\Model;

use Magento\Seller\Model\Data\SellerSecure;
use Magento\Seller\Model\Data\SellerSecureFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Registry for \Magento\Seller\Model\Seller
 */
class SellerRegistry
{
    const REGISTRY_SEPARATOR = ':';

    /**
     * @var SellerFactory
     */
    private $sellerFactory;

    /**
     * @var SellerSecureFactory
     */
    private $sellerSecureFactory;

    /**
     * @var array
     */
    private $sellerRegistryById = [];

    /**
     * @var array
     */
    private $sellerRegistryByEmail = [];

    /**
     * @var array
     */
    private $sellerSecureRegistryById = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param SellerFactory $sellerFactory
     * @param SellerSecureFactory $sellerSecureFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SellerFactory $sellerFactory,
        SellerSecureFactory $sellerSecureFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->sellerSecureFactory = $sellerSecureFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve Seller Model from registry given an id
     *
     * @param string $sellerId
     * @return Seller
     * @throws NoSuchEntityException
     */
    public function retrieve($sellerId)
    {
        if (isset($this->sellerRegistryById[$sellerId])) {
            return $this->sellerRegistryById[$sellerId];
        }
        /** @var Seller $seller */
        $seller = $this->sellerFactory->create()->load($sellerId);
        if (!$seller->getId()) {
            // seller does not exist
            throw NoSuchEntityException::singleField('sellerId', $sellerId);
        } else {
            $emailKey = $this->getEmailKey($seller->getEmail(), $seller->getWebsiteId());
            $this->sellerRegistryById[$sellerId] = $seller;
            $this->sellerRegistryByEmail[$emailKey] = $seller;
            return $seller;
        }
    }

    /**
     * Retrieve Seller Model from registry given an email
     *
     * @param string $sellerEmail Sellers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return Seller
     * @throws NoSuchEntityException
     */
    public function retrieveByEmail($sellerEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId()
                ?: $this->storeManager->getDefaultStoreView()->getWebsiteId();
        }

        $emailKey = $this->getEmailKey($sellerEmail, $websiteId);
        if (isset($this->sellerRegistryByEmail[$emailKey])) {
            return $this->sellerRegistryByEmail[$emailKey];
        }

        /** @var Seller $seller */
        $seller = $this->sellerFactory->create();

        if (isset($websiteId)) {
            $seller->setWebsiteId($websiteId);
        }

        $seller->loadByEmail($sellerEmail);
        if (!$seller->getEmail()) {
            // seller does not exist
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'email',
                        'fieldValue' => $sellerEmail,
                        'field2Name' => 'websiteId',
                        'field2Value' => $websiteId
                    ]
                )
            );
        } else {
            $this->sellerRegistryById[$seller->getId()] = $seller;
            $this->sellerRegistryByEmail[$emailKey] = $seller;
            return $seller;
        }
    }

    /**
     * Retrieve SellerSecure Model from registry given an id
     *
     * @param int $sellerId
     * @return SellerSecure
     * @throws NoSuchEntityException
     */
    public function retrieveSecureData($sellerId)
    {
        if (isset($this->sellerSecureRegistryById[$sellerId])) {
            return $this->sellerSecureRegistryById[$sellerId];
        }
        /** @var Seller $seller */
        $seller = $this->retrieve($sellerId);
        /** @var $sellerSecure SellerSecure*/
        $sellerSecure = $this->sellerSecureFactory->create();
        $sellerSecure->setPasswordHash($seller->getPasswordHash());
        $sellerSecure->setRpToken($seller->getRpToken());
        $sellerSecure->setRpTokenCreatedAt($seller->getRpTokenCreatedAt());
        $sellerSecure->setDeleteable($seller->isDeleteable());
        $sellerSecure->setFailuresNum($seller->getFailuresNum());
        $sellerSecure->setFirstFailure($seller->getFirstFailure());
        $sellerSecure->setLockExpires($seller->getLockExpires());
        $this->sellerSecureRegistryById[$seller->getId()] = $sellerSecure;

        return $sellerSecure;
    }

    /**
     * Remove instance of the Seller Model from registry given an id
     *
     * @param int $sellerId
     * @return void
     */
    public function remove($sellerId)
    {
        if (isset($this->sellerRegistryById[$sellerId])) {
            /** @var Seller $seller */
            $seller = $this->sellerRegistryById[$sellerId];
            $emailKey = $this->getEmailKey($seller->getEmail(), $seller->getWebsiteId());
            unset($this->sellerRegistryByEmail[$emailKey]);
            unset($this->sellerRegistryById[$sellerId]);
            unset($this->sellerSecureRegistryById[$sellerId]);
        }
    }

    /**
     * Remove instance of the Seller Model from registry given an email
     *
     * @param string $sellerEmail Sellers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return void
     */
    public function removeByEmail($sellerEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($sellerEmail, $websiteId);
        if (isset($this->sellerRegistryByEmail[$emailKey])) {
            /** @var Seller $seller */
            $seller = $this->sellerRegistryByEmail[$emailKey];
            unset($this->sellerRegistryByEmail[$emailKey]);
            unset($this->sellerRegistryById[$seller->getId()]);
            unset($this->sellerSecureRegistryById[$seller->getId()]);
        }
    }

    /**
     * Create registry key
     *
     * @param string $sellerEmail
     * @param string $websiteId
     * @return string
     */
    protected function getEmailKey($sellerEmail, $websiteId)
    {
        return $sellerEmail . self::REGISTRY_SEPARATOR . $websiteId;
    }

    /**
     * Replace existing seller model with a new one.
     *
     * @param Seller $seller
     * @return $this
     */
    public function push(Seller $seller)
    {
        $this->sellerRegistryById[$seller->getId()] = $seller;
        $emailKey = $this->getEmailKey($seller->getEmail(), $seller->getWebsiteId());
        $this->sellerRegistryByEmail[$emailKey] = $seller;
        return $this;
    }
}
