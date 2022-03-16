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
    const CUSTOMER_ADDRESS_ENTITY_RESOURCE = 'Customer_Address_Entity';
    const CUSTOMER_ENTITY_SALES_ORDER_RESOURCE = 'Customer_Entity_Sales_Order';

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface                 $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder               $searchCriteriaBuilder,
        \Magento\Customer\Model\CustomerFactory                    $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection  $customerCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface          $customerRepository,
        \Magento\Customer\Model\AddressFactory                     $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface           $addressRepository,
        \Magento\Integration\Model\Oauth\TokenFactory              $tokenModelFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Psr\Log\LoggerInterface                                   $logger,
        \Magento\Framework\Math\Random                             $mathRandom,
        \Magento\Framework\Intl\DateTimeFactory                    $dateTimeFactory,
        array                                                      $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->_storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->mathRandom = $mathRandom;
        $this->customerCollection = $customerCollection;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->addressFactory = $addressFactory;
        $this->orderCollection = $orderCollection;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
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
                // Check if mobile number already exists
                $customerIdByMobileNumber = $this->customerFactory->create()->getCollection()->addAttributeToSelect('id')
                    ->addAttributeToFilter('mobile_number', $mobileNumber)
                    ->load()
                    ->getFirstItem()->getData('entity_id');
                if ($customerIdByMobileNumber) {
                    $response['message'] = 'Customer Mobile Number Already Registered, Please Signup with a different email or login';
                    return $response;
                }

                // Check if email already exists
                $customerIdByEmail = $this->customerFactory->create()->getCollection()->addAttributeToSelect('id')
                    ->addAttributeToFilter('email', $email)
                    ->load()
                    ->getFirstItem()->getData('entity_id');
                if ($customerIdByEmail) {
                    $response['message'] = 'Customer Email Already Registered, Please Signup with a different email or login';
                    return $response;
                }

                // Customer with unique email and unique mobile number
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
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ENTITY_RESOURCE,
            'message' => 'Unable to retrieve customer data',
            'data' => $customerId
        ];
        try {
            $customer = $this->customerRepository->getById($customerId);
            $response['message'] = 'success';
            $response['data'] = [
                'firstname' => $customer->getFirstname(),
                'middlename' => $customer->getMiddlename(),
                'lastname' => $customer->getLastname(),
                'dob' => $customer->getDob(),
                'gender' => ($customer->getGender() == "1" || $customer->getGender() == 1) ? 'male' : 'female',
                'mobile_number' => $customer->getCustomAttribute('mobile_number')->getValue(),
                'billing_address' => $this->getCustomerAddressByAddressId($customer->getDefaultBilling()),
                'shipping_address' => $this->getCustomerAddressByAddressId($customer->getDefaultShipping()),
            ];
            return $response;
        } catch (Exception $e) {
            return $response;
        }
        return $response;
    }

    public function getCustomerAddressByAddressId($addressId)
    {
        try {
            $address = $this->addressFactory->create()->load($addressId)->getData();
            return [
                'id' => $address['entity_id'],
                'firstname' => $address['firstname'],
                'middlename' => $address['middlename'],
                'lastname' => $address['lastname'],
                'street' => $address['street'],
                'city' => $address['city'],
                'pincode' => $address['postcode'],
                'contact_phone' => $address['telephone'],
            ];
        } catch (Exception $e) {
            return new \stdClass();
        }
    }

    public function getCustomerOrderDetailsById($customerId)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ENTITY_SALES_ORDER_RESOURCE,
            'message' => 'Unable to retrieve customer orders',
            'data' => []
        ];
        try {
            $customerOrder = $this->orderCollection->create()
                ->addFieldToFilter('customer_id', $customerId);
            $response['message'] = 'success';
            $response['data'] = $customerOrder->getData();
            return $response;
        } catch (Exception $e) {
            return $response;
        }
        return $response;
    }

    public function getCustomerAddresses($customerId)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ADDRESS_ENTITY_RESOURCE,
            'message' => 'Unable to retrieve customer addresses',
            'data' => []
        ];
        try {
            $addressesList = [];
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'parent_id', $customerId)->create();
            $addressRepository = $this->addressRepository->getList($searchCriteria);
            foreach ($addressRepository->getItems() as $address) {
                $addressesList[] = [
                    'firstname' => $address->getFirstname(),
                    'middlename' => $address->getMiddlename(),
                    'lastname' => $address->getLastname(),
                    'street' => $address->getStreet(),
                    'city' => $address->getCity(),
                    'state' => $address->getRegion()->getRegion(),
                    'pincode' => $address->getPostcode(),
                    'contact_phone' => $address->getTelephone(),
                ];
            }
            $response['data'] = $addressesList;
            $response['message'] = 'success';
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $response;
    }

    public function getCustomerWalletBalance($customerId)
    {
        // TODO: Implement getCustomerWalletBalance() method.
    }

    public function setCustomerBillingAddress($customerId, $address)
    {
        // TODO: Implement setCustomerBillingAddress() method.
    }
}
