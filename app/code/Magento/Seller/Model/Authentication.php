<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Model\ResourceModel\SellerRepository;
use Magento\Seller\Model\SellerAuthUpdate;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * Class Authentication
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authentication implements AuthenticationInterface
{
    /**
     * Configuration path to seller lockout threshold
     */
    const LOCKOUT_THRESHOLD_PATH = 'seller/password/lockout_threshold';

    /**
     * Configuration path to seller max login failures number
     */
    const MAX_FAILURES_PATH = 'seller/password/lockout_failures';

    /**
     * @var SellerRegistry
     */
    protected $sellerRegistry;

    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $backendConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var Encryptor
     */
    protected $encryptor;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var SellerAuthUpdate
     */
    private $sellerAuthUpdate;

    /**
     * @param SellerRepositoryInterface $sellerRepository
     * @param SellerRegistry $sellerRegistry
     * @param ConfigInterface $backendConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param Encryptor $encryptor
     */
    public function __construct(
        SellerRepositoryInterface $sellerRepository,
        SellerRegistry $sellerRegistry,
        ConfigInterface $backendConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Encryptor $encryptor
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->sellerRegistry = $sellerRegistry;
        $this->backendConfig = $backendConfig;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function processAuthenticationFailure($sellerId)
    {
        $now = new \DateTime();
        $lockThreshold = $this->getLockThreshold();
        $maxFailures =  $this->getMaxFailures();
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($sellerId);

        if (!($lockThreshold && $maxFailures)) {
            return;
        }
        $failuresNum = (int)$sellerSecure->getFailuresNum() + 1;

        $firstFailureDate = $sellerSecure->getFirstFailure();
        if ($firstFailureDate) {
            $firstFailureDate = new \DateTime($firstFailureDate);
        }

        $lockThreshInterval = new \DateInterval('PT' . $lockThreshold . 'S');
        $lockExpires = $sellerSecure->getLockExpires();
        $lockExpired = ($lockExpires !== null) && ($now > new \DateTime($lockExpires));
        // set first failure date when this is the first failure or the lock is expired
        if (1 === $failuresNum || !$firstFailureDate || $lockExpired) {
            $sellerSecure->setFirstFailure($this->dateTime->formatDate($now));
            $failuresNum = 1;
            $sellerSecure->setLockExpires(null);
            // otherwise lock seller
        } elseif ($failuresNum >= $maxFailures) {
            $sellerSecure->setLockExpires($this->dateTime->formatDate($now->add($lockThreshInterval)));
        }

        $sellerSecure->setFailuresNum($failuresNum);
        $this->getSellerAuthUpdate()->saveAuth($sellerId);
    }

    /**
     * @inheritdoc
     */
    public function unlock($sellerId)
    {
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($sellerId);
        $sellerSecure->setFailuresNum(0);
        $sellerSecure->setFirstFailure(null);
        $sellerSecure->setLockExpires(null);
        $this->getSellerAuthUpdate()->saveAuth($sellerId);
    }

    /**
     * Get lock threshold
     *
     * @return int
     */
    protected function getLockThreshold()
    {
        return $this->backendConfig->getValue(self::LOCKOUT_THRESHOLD_PATH) * 60;
    }

    /**
     * Get max failures
     *
     * @return int
     */
    protected function getMaxFailures()
    {
        return $this->backendConfig->getValue(self::MAX_FAILURES_PATH);
    }

    /**
     * @inheritdoc
     */
    public function isLocked($sellerId)
    {
        $currentSeller = $this->sellerRegistry->retrieve($sellerId);
        return $currentSeller->isSellerLocked();
    }

    /**
     * @inheritdoc
     */
    public function authenticate($sellerId, $password)
    {
        $sellerSecure = $this->sellerRegistry->retrieveSecureData($sellerId);
        $hash = $sellerSecure->getPasswordHash() ?? '';
        if (!$this->encryptor->validateHash($password, $hash)) {
            $this->processAuthenticationFailure($sellerId);
            if ($this->isLocked($sellerId)) {
                throw new UserLockedException(__('The account is locked.'));
            }
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        return true;
    }

    /**
     * Get seller authentication update model
     *
     * @return \Magento\Seller\Model\SellerAuthUpdate
     * @deprecated 100.1.1
     */
    private function getSellerAuthUpdate()
    {
        if ($this->sellerAuthUpdate === null) {
            $this->sellerAuthUpdate =
                \Magento\Framework\App\ObjectManager::getInstance()->get(SellerAuthUpdate::class);
        }
        return $this->sellerAuthUpdate;
    }
}
