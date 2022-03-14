<?php

namespace Honasa\Customer\Model;

use Exception;
use Honasa\Customer\Api\CustomerEntityInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;


/**
 * Defines the implementaiton class of the calculator service contract.
 */
class CustomerEntity implements CustomerEntityInterface
{
    const CUSTOMER_ENTITY_RESOURCE = 'Customer_Entity';

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface        $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder      $searchCriteriaBuilder,
        \Magento\Customer\Model\CustomerFactory           $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Integration\Model\Oauth\TokenFactory     $tokenModelFactory,
        \Magento\Framework\Math\Random                    $mathRandom,
        \Magento\Framework\Intl\DateTimeFactory           $dateTimeFactory,
        array                                             $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->_storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->mathRandom = $mathRandom;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function registerCustomer($data)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ENTITY_RESOURCE,
            'message' => 'Data Parameters: firstName/lastName/email/mobileNumber/gendermissing',
            'data' => []
        ];
        try {
            $firstName = $data['firstName'];
            $lastName = $data['lastName'];
            $mobileNumber = $data['mobile_number'];
            $email = $data['email'];
            $gender = $data['gender'];
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            if (
                isset($email) &&
                isset($firstName) &&
                isset($lastName) &&
                isset($gender) &&
                isset($mobileNumber)
            ) {
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setLastname($lastName);
                $customer->setDefaultBilling(0);
                $customer->setDefaultShipping(0);
                $customer->setForceConfirmed(true);
                switch (strtoupper($gender)) {
                    case 'FEMALE':
                        $customer->setGender(2);
                        break;
                    case 'MALE':
                        $customer->setGender(1);
                        break;
                    default:
                        $customer->setGender(0);
                        break;
                }
                $rpTokenHash = $this->mathRandom->getUniqueHash();
                $customer->setRpToken($rpTokenHash);
                $limitRpTokenDate = $this->dateTimeFactory->create()->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                $customer->setRpTokenCreatedAt($limitRpTokenDate);
                $customerDataModel = $customer->getDataModel();
                $customerDataModel->setCustomAttribute('mobile_number', $mobileNumber);
                $customer->updateData($customerDataModel);
                $customer->save();
                $customerId = $customer->getId();

                $customerToken = $this->tokenModelFactory->create();
                $token = $customerToken->createCustomerToken($customerId)->getToken();


                return [
                    'status' => 201,
                    'resource' => self::CUSTOMER_ENTITY_RESOURCE,
                    'message' => 'success',
                    'data' => [
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'gender' => $gender,
                        'mobileNumber' => $mobileNumber,
                        'token' => $token
                    ]
                ];
            }
            return $response;
        } catch (Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Customer Registration Failed')
            );
        }
    }

    public function getCustomerDetailsById($customerId)
    {
        // TODO: Implement getCustomerDetailsById() method.
    }

    public function getCustomerOrderDetailsById($customerId)
    {
        // TODO: Implement getCustomerOrderDetailsById() method.
    }

    public function getCustomerAddresses($customerId)
    {
        // TODO: Implement getCustomerAddresses() method.
    }

    public function getCustomerWalletBalance($customerId)
    {
        // TODO: Implement getCustomerWalletBalance() method.
    }
}
