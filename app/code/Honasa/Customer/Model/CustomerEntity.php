<?php

namespace Honasa\Customer\Model;

use Exception;
use Honasa\Customer\Api\CustomerEntityInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection  as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Honasa\Base\Model\Data\ResponseFactory;
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
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerFactory $customerFactory,
        CustomerCollection $customerCollection,
        CustomerResource $customerResource,
        CustomerRepositoryInterface $customerRepository,
        AddressFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionCollection $regionCollection,
        RegionCollectionFactory $regionFactory,
        TokenFactory $tokenModelFactory,
        OrderCollectionFactory $orderCollection,
        LoggerInterface $logger,
        Random $mathRandom,
        DateTimeFactory $dateTimeFactory,
        TimezoneInterface $timezone,
        AccountManagementInterface  $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        ManagerInterface $eventManager = null,
        ResponseFactory $responseFactory
        ){
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
        $this->addressDataFactory = $addressDataFactory;
        $this->regionCollection = $regionCollection;
        $this->regionFactory = $regionFactory;
        $this->timezone = $timezone;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        $this->customerResource = $customerResource;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->responseFactory = $responseFactory;

    }

    public function registerCustomer($data)
    {
        $response = $this->responseFactory->create();
        $response->setResource(self::CUSTOMER_ENTITY_RESOURCE);

        try {
            $firstName = isset($data['firstname'] ) ? $data['firstname'] : '';
            $lastName = isset($data['lastname'] ) ? $data['lastname'] : '';
            $mobileNumber = isset($data['mobile_number'] ) ? $data['mobile_number'] : '';
            $email = isset($data['email'] ) ? $data['email'] : '';
            $gender = isset($data['gender']) ? $data['gender'] : 0;
            $dob = isset($data['dob']) ? $data['dob'] : null;
            $websiteId = $this->storeManager->getStore()->getWebsiteId();

            if (
                !is_null($email) &&
                !is_null($firstName) &&
                !is_null($lastName) &&
                !is_null($gender) &&
                !is_null($mobileNumber)
            ) {
                // Check if mobile number already exists
                $customerIdByMobileNumber = $this->customerFactory->create()->getCollection()->addAttributeToSelect('id')
                    ->addAttributeToFilter('mobile_number', $mobileNumber)
                    ->load()
                    ->getFirstItem()->getData('entity_id');
                if ($customerIdByMobileNumber) {
                    $response->setMessage('Customer Mobile Number Already Registered, Please Signup with a different email or login');
                    return $response;
                }

                // Check if email already exists
                $customerIdByEmail = $this->customerFactory->create()->getCollection()->addAttributeToSelect('id')
                    ->addAttributeToFilter('email', $email)
                    ->load()
                    ->getFirstItem()->getData('entity_id');
                if ($customerIdByEmail) {
                    $response->setMessage('Customer Email Already Registered, Please Signup with a different email or login');
                    return $response;
                }

                // Customer with unique email and unique mobile number
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($websiteId);
                $customer->setEmail($email);
                $customer->setFirstname($firstName);
                $customer->setGender($gender);
                if(!is_null($dob))
                {
                    $customer->setDob($dob);
                }
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
                $response->setStatus(true);
                $response->setMessage('success');
                $response->setData([
                    'customer_id' => $customerId,
                    'token' => $token,
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'email' => $email,
                    'mobile_number' => $mobileNumber
                ]);

               
            }
            return (array) $response;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $response->setMessage($e->getMessage());
        }
        return $response;
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

    public function loginCustomer($username, $password)
    {
        $response = [
            'status' => 200,
            'resource' => self::CUSTOMER_ENTITY_RESOURCE,
            'message' => self::RESPONSE_MESSAGE_FAILURE,
            'data' => []
        ];
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
        try {

            $customerDataObject = $this->accountManagement->authenticate($username, $password);
            $this->eventManager->dispatch('customer_login', ['customer' => $customerDataObject]);
            $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_CUSTOMER);
            $response['status'] = self::RESPONSE_MESSAGE_SUCCESS;
            $response['data'] = [
                'me' => $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken()
            ];

            return $response;
        } catch (\Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }

    }
}