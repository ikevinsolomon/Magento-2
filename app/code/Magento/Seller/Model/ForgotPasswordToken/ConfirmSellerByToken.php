<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\ForgotPasswordToken;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Confirm seller by reset password token
 */
class ConfirmSellerByToken
{
    /**
     * @var GetSellerByToken
     */
    private $getByToken;

    /**
     * @var SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @param GetSellerByToken $getByToken
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(GetSellerByToken $getByToken, SellerRepositoryInterface $sellerRepository)
    {
        $this->getByToken = $getByToken;
        $this->sellerRepository = $sellerRepository;
    }

    /**
     * Confirm seller account my rp_token
     *
     * @param string $resetPasswordToken
     * @return void
     * @throws LocalizedException
     */
    public function execute(string $resetPasswordToken): void
    {
        $seller = $this->getByToken->execute($resetPasswordToken);
        if ($seller->getConfirmation()) {
            $this->resetConfirmation($seller);
        }
    }

    /**
     * Reset seller confirmation
     *
     * @param SellerInterface $seller
     * @return void
     */
    private function resetConfirmation(SellerInterface $seller): void
    {
        // skip unnecessary address and seller validation
        $seller->setData('ignore_validation_flag', true);
        $seller->setConfirmation(null);

        $this->sellerRepository->save($seller);
    }
}
