<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Seller\Model\Plugin;

use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Seller\Api\SellerRepositoryInterface;

/**
 * Update seller by id from request param
 */
class UpdateSeller
{
    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @param RestRequest $request
     */
    public function __construct(RestRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Update seller by id from request if exist
     *
     * @param SellerRepositoryInterface $sellerRepository
     * @param SellerInterface $seller
     * @param string|null $passwordHash
     * @return array
     */
    public function beforeSave(
        SellerRepositoryInterface $sellerRepository,
        SellerInterface $seller,
        ?string $passwordHash = null
    ): array {
        $sellerId = $this->request->getParam('sellerId');
        $bodyParams = $this->request->getBodyParams();
        if (!isset($bodyParams['seller']['Id']) && $sellerId) {
            $seller = $this->getUpdatedSeller($sellerRepository->getById($sellerId), $seller);
        }

        return [$seller, $passwordHash];
    }

    /**
     * Return updated seller
     *
     * @param SellerInterface $originSeller
     * @param SellerInterface $seller
     * @return SellerInterface
     */
    private function getUpdatedSeller(
        SellerInterface $originSeller,
        SellerInterface $seller
    ): SellerInterface {
        $newSeller = clone $originSeller;
        foreach ($seller->__toArray() as $name => $value) {
            if ($name === SellerInterface::CUSTOM_ATTRIBUTES) {
                $value = $seller->getCustomAttributes();
            } elseif ($name === SellerInterface::EXTENSION_ATTRIBUTES_KEY) {
                $value = $seller->getExtensionAttributes();
            } elseif ($name === SellerInterface::KEY_ADDRESSES) {
                $value = $seller->getAddresses();
            }

            $newSeller->setData($name, $value);
        }

        return $newSeller;
    }
}
