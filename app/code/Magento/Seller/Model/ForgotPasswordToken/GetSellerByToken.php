<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Model\ForgotPasswordToken;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;

/**
 * Get Seller By reset password token
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GetSellerByToken
{
    /**
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    private $sellerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * ForgotPassword constructor.
     *
     * @param \Magento\Seller\Api\SellerRepositoryInterface $sellerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SellerRepositoryInterface $sellerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get seller by rp_token
     *
     * @param string $resetPasswordToken
     *
     * @return \Magento\Seller\Api\Data\SellerInterface
     * @throws ExpiredException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $resetPasswordToken): SellerInterface
    {
        $this->searchCriteriaBuilder->addFilter(
            'rp_token',
            $resetPasswordToken
        );
        $this->searchCriteriaBuilder->setPageSize(1);
        $found = $this->sellerRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($found->getTotalCount() > 1) {
            //Failed to generated unique RP token
            throw new ExpiredException(
                new Phrase('Reset password token expired.')
            );
        }
        if ($found->getTotalCount() === 0) {
            //Seller with such token not found.
            throw new NoSuchEntityException(
                new Phrase(
                    'No such entity with rp_token = %value',
                    [
                        'value' => $resetPasswordToken
                    ]
                )
            );
        }

        //Unique seller found.
        return $found->getItems()[0];
    }
}
