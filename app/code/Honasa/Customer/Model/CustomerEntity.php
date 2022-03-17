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
    const CUSTOMER_ENTITY_STORE_CREDIT = 'Customer_Entity_Store_Credit';
    const RESPONSE_MESSAGE_SUCCESS = 'success';
    const RESPONSE_MESSAGE_FAILURE = 'failure';

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface                      $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder                    $searchCriteriaBuilder,
        \Magento\Customer\Model\CustomerFactory                         $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer\Collection       $customerCollection,
        \Magento\Customer\Model\ResourceModel\Customer                  $customerResource,
        \Magento\Customer\Api\CustomerRepositoryInterface               $customerRepository,
        \Magento\Customer\Model\AddressFactory                          $addressFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory              $addressInterfaceFactory,
        \Magento\Customer\Api\AddressRepositoryInterface                $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory              $addressDataFactory,
        \Magento\Directory\Model\ResourceModel\Region\Collection        $regionCollection,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionFactory,
        \Magento\Integration\Model\Oauth\TokenFactory                   $tokenModelFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory      $orderCollection,
        \Psr\Log\LoggerInterface                                        $logger,
        \Magento\Framework\Math\Random                                  $mathRandom,
        \Magento\Framework\Intl\DateTimeFactory                         $dateTimeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface            $timezone,
        array                                                           $data = []
    )
    {
        $this->storeManager = $storeManager;
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
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionCollection = $regionCollection;
        $this->regionFactory = $regionFactory;
        $this->timezone = $timezone;
        $this->customerResource = $customerResource;
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
            $websiteId = $this->storeManager->getStore()->getWebsiteId();

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
            $this->logger->error($e->getMessage());
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
            $response['message'] = self::RESPONSE_MESSAGE_SUCCESS;
            $response['data'] = [
                'firstname' => $customer->getFirstname(),
                'middlename' => $customer->getMiddlename(),
                'lastname' => $customer->getLastname(),
                'email' => $customer->getEmail(),
                'dob' => $customer->getDob(),
                'gender' => ($customer->getGender() == "1" || $customer->getGender() == 1) ? 'male' : 'female',
                'mobile_number' => $customer->getCustomAttribute('mobile_number')->getValue(),
                'billing_address' => $this->getCustomerAddressByAddressId($customer->getDefaultBilling()),
                'shipping_address' => $this->getCustomerAddressByAddressId($customer->getDefaultShipping()),
            ];
            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
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
            $this->logger->error($e->getMessage());
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
            $response['message'] = self::RESPONSE_MESSAGE_SUCCESS;
            $response['data'] = $customerOrder->getData();
            return $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
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
            $customer = $this->customerRepository->getById($customerId);
            $customerAddresses = $customer->getAddresses();
            foreach ($customerAddresses as $address) {
                $addressesList[] = [
                    'firstname' => $address->getFirstname(),
                    'middlename' => $address->getMiddlename(),
                    'lastname' => $address->getLastname(),
                    'street' => $address->getStreet(),
                    'city' => $address->getCity(),
                    'state' => $address->getRegion()->getRegion(),
                    'pincode' => $address->getPostcode(),
                    'contact_phone' => $address->getTelephone(),
                    'is_default_billing' => $address->isDefaultBilling() === true,
                    'is_default_shipping' => $address->isDefaultShipping() === true
                ];
            }
            $response['message'] = self::RESPONSE_MESSAGE_SUCCESS;
            $response['data'] = $addressesList;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $response;
    }

    public function getCustomerWalletBalance($customerId)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ENTITY_STORE_CREDIT,
            'message' => 'Unable to retrieve customer store credit',
            'data' => []
        ];

        return $response;
    }

    public function setCustomerAddress($customerId, $address)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ADDRESS_ENTITY_RESOURCE,
            'message' => 'setCustomerAddress',
            'data' => []
        ];

        try {

            $firstName = $address['firstname'];
            $lastName = $address['lastname'];
            $mobileNumber = $address['mobile_number'];
            $streetLineOne = $address['street_line_one'];
            $streetLineTwo = $address['street_line_two'];
            $city = $address['city'];
            $region = $address['state'];
            $postCode = $address['pincode'];
            $isDefaultBilling = $address['is_default_billing'];
            $isDefaultShipping = $address['is_default_shipping'];
            if (
                isset($firstName) &&
                isset($lastName) &&
                isset($mobileNumber) &&
                isset($streetLineOne) &&
                isset($streetLineTwo) &&
                isset($city) &&
                isset($region) &&
                isset($postCode)

            ) {

                $firstName = $address['firstname'];
                $lastName = $address['lastname'];
                $mobileNumber = $address['mobile_number'];
                $streetLineOne = $address['street_line_one'];
                $streetLineTwo = $address['street_line_two'];
                $city = $address['city'];
                $region = $address['state'];
                $postCode = $address['pincode'];
                if (
                    isset($firstName) &&
                    isset($lastName) &&
                    isset($mobileNumber) &&
                    isset($streetLineOne) &&
                    isset($streetLineTwo) &&
                    isset($city) &&
                    isset($region) &&
                    isset($postCode)
                ) {
                    $addressEntry = $this->addressDataFactory->create();
                    $addressEntry->setFirstname($firstName);
                    $addressEntry->setLastname($lastName);
                    $addressEntry->setTelephone($mobileNumber);

                    $street[] = $streetLineOne;
                    $street[] = $streetLineTwo;
                    $addressEntry->setStreet($street);
                    $addressEntry->setCity($city);
                    $addressEntry->setPostcode($postCode);

                    $regionInfo = $this->regionFactory->create()
                        ->addRegionNameFilter($region)
                        ->getFirstItem()
                        ->toArray();
                    $addressEntry->setRegionId($regionInfo['region_id']);
                    $addressEntry->setCountryId($regionInfo['country_id']);
                    $addressEntry->setIsDefaultBilling($isDefaultBilling);
                    $addressEntry->setIsDefaultShipping($isDefaultShipping);
                    $addressEntry->setCustomerId($customerId);
                    $this->addressRepository->save($addressEntry);
                    $response['status'] = 201;
                    $response['message'] = self::RESPONSE_MESSAGE_SUCCESS;
                    $response['data'] = $address;
                    return $response;
                }

                return $response;
            }

            return $response;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        $response['message'] = 'Either address[firstname|lastname|mobile_number|street_line_one|street_line_two|city|state|pincode] missing';
        return $response;

    }

    public function setCustomerDetailsById($customerId, $data, $update = true)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ENTITY_RESOURCE,
            'message' => 'Update Customer Details Failed',
            'data' => []
        ];

        $customerRepository = $this->customerRepository->getById($customerId);
        $customerEmail = $customerRepository->getEmail();

        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerFactory = $this->customerFactory->create();

        //load existing customer to update its attribute
        if ($update) {
            $customerFactory->setWebsiteId($websiteId)->loadByEmail($customerEmail);
        }

        $customerFactory->setWebsiteId($websiteId)
            ->setStore($store)
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setForceConfirmed(true);

        // Gender
        switch (strtoupper($data['gender'])) {
            case 'FEMALE':
                $customerFactory->setGender(2);
                break;
            case 'MALE':
                $customerFactory->setGender(1);
                break;
            default:
                $customerFactory->setGender(0);
                break;
        }

        $dob = $data['dob'];
        if (!empty($dob)) {
            $customerFactory->setDob(date("Y-m-d", strtotime($dob)));
        }
        try {
            //update customer
            $this->customerResource->save($customerFactory);
            $response['message'] = self::RESPONSE_MESSAGE_SUCCESS;
            $response['data'] = [
                'firstname' => $customerFactory->getFirstname(),
                'middlename' => $customerFactory->getMiddlename(),
                'lastname' => $customerFactory->getLastname(),
                'email' => $customerFactory->getEmail(),
                'dob' => $customerFactory->getDob(),
                'gender' => ($customerFactory->getGender() == "1" || $customerFactory->getGender() == 1) ? 'male' : 'female',
                'mobile_number' => $customerFactory->getMobileNumber()
            ];
            return $response;
        } catch (AlreadyExistsException $e) {
            $this->logger->error($e->getMessage());
            return $response;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $response;
        }

    }
}
