<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model;

use Magento\Seller\Api\SellerRepositoryInterface;
use Magento\Seller\Api\Data\SellerInterface as SellerData;
use Magento\Seller\Api\GroupManagementInterface;
use Magento\Seller\Model\Config\Share;
use Magento\Seller\Model\ResourceModel\Seller as ResourceSeller;
use Magento\Framework\App\ObjectManager;

/**
 * Seller session model
 *
 * @api
 * @method string getNoReferer()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Session extends \Magento\Framework\Session\SessionManager
{
    /**
     * Seller object
     *
     * @var SellerData
     */
    protected $_seller;

    /**
     * @var ResourceSeller
     */
    protected $_sellerResource;

    /**
     * Seller model
     *
     * @var Seller
     */
    protected $_sellerModel;

    /**
     * Flag with seller id validations result
     *
     * @var bool|null
     */
    protected $_isSellerIdChecked = null;

    /**
     * Seller URL
     *
     * @var \Magento\Seller\Model\Url
     */
    protected $_sellerUrl;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data|null
     */
    protected $_coreUrl = null;

    /**
     * @var Share
     */
    protected $_configShare;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var  SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var SellerFactory
     */
    protected $_sellerFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    /**
     * Session constructor.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param Share $configShare
     * @param \Magento\Framework\Url\Helper\Data $coreUrl
     * @param Url $sellerUrl
     * @param ResourceSeller $sellerResource
     * @param SellerFactory $sellerFactory
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SellerRepositoryInterface $sellerRepository
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\App\Response\Http $response
     * @param AccountConfirmation $accountConfirmation
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        Config\Share $configShare,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        \Magento\Seller\Model\Url $sellerUrl,
        ResourceSeller $sellerResource,
        SellerFactory $sellerFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Http\Context $httpContext,
        SellerRepositoryInterface $sellerRepository,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\App\Response\Http $response,
        AccountConfirmation $accountConfirmation = null
    ) {
        $this->_coreUrl = $coreUrl;
        $this->_sellerUrl = $sellerUrl;
        $this->_configShare = $configShare;
        $this->_sellerResource = $sellerResource;
        $this->_sellerFactory = $sellerFactory;
        $this->_urlFactory = $urlFactory;
        $this->_session = $session;
        $this->sellerRepository = $sellerRepository;
        $this->_eventManager = $eventManager;
        $this->_httpContext = $httpContext;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
        $this->groupManagement = $groupManagement;
        $this->response = $response;
        $this->accountConfirmation = $accountConfirmation ?: ObjectManager::getInstance()
            ->get(AccountConfirmation::class);
        $this->_eventManager->dispatch('seller_session_init', ['seller_session' => $this]);
    }

    /**
     * Retrieve seller sharing configuration model
     *
     * @return Share
     */
    public function getSellerConfigShare()
    {
        return $this->_configShare;
    }

    /**
     * Set seller object and setting seller id in session
     *
     * @param   SellerData $seller
     * @return  $this
     */
    public function setSellerData(SellerData $seller)
    {
        $this->_seller = $seller;
        if ($seller === null) {
            $this->setSellerId(null);
        } else {
            $this->_httpContext->setValue(
                Context::CONTEXT_GROUP,
                $seller->getGroupId(),
                \Magento\Seller\Model\Group::NOT_LOGGED_IN_ID
            );
            $this->setSellerId($seller->getId());
        }
        return $this;
    }

    /**
     * Retrieve seller model object
     *
     * @return SellerData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSellerData()
    {
        if (!$this->_seller instanceof SellerData && $this->getSellerId()) {
            $this->_seller = $this->sellerRepository->getById($this->getSellerId());
        }

        return $this->_seller;
    }

    /**
     * Returns Seller data object with the seller information
     *
     * @return SellerData
     */
    public function getSellerDataObject()
    {
        /* TODO refactor this after all usages of the setSeller is refactored */
        return $this->getSeller()->getDataModel();
    }

    /**
     * Set Seller data object with the seller information
     *
     * @param SellerData $sellerData
     * @return $this
     */
    public function setSellerDataObject(SellerData $sellerData)
    {
        $this->setId($sellerData->getId());
        $this->getSeller()->updateData($sellerData);
        return $this;
    }

    /**
     * Set seller model and the seller id in session
     *
     * @param   Seller $sellerModel
     * @return  $this
     * use setSellerId() instead
     */
    public function setSeller(Seller $sellerModel)
    {
        $this->_sellerModel = $sellerModel;
        $this->_httpContext->setValue(
            Context::CONTEXT_GROUP,
            $sellerModel->getGroupId(),
            \Magento\Seller\Model\Group::NOT_LOGGED_IN_ID
        );
        $this->setSellerId($sellerModel->getId());
        $accountConfirmationRequired = $this->accountConfirmation->isConfirmationRequired(
            $sellerModel->getWebsiteId(),
            $sellerModel->getId(),
            $sellerModel->getEmail()
        );
        if (!$accountConfirmationRequired && $sellerModel->getConfirmation() && $sellerModel->getId()) {
            $sellerModel->setConfirmation(null);
            $this->_sellerResource->save($sellerModel);
        }

        /**
         * The next line is a workaround.
         * It is used to distinguish users that are logged in from user data set via methods similar to setSellerId()
         */
        $this->unsIsSellerEmulated();

        return $this;
    }

    /**
     * Retrieve seller model object
     *
     * @return Seller
     * use getSellerId() instead
     */
    public function getSeller()
    {
        if ($this->_sellerModel === null) {
            $this->_sellerModel = $this->_sellerFactory->create();

            if ($this->getSellerId()) {
                $this->_sellerResource->load($this->_sellerModel, $this->getSellerId());
            }
        }

        return $this->_sellerModel;
    }

    /**
     * Set seller id
     *
     * @param int|null $id
     * @return $this
     */
    public function setSellerId($id)
    {
        $this->storage->setData('seller_id', $id);
        return $this;
    }

    /**
     * Retrieve seller id from current session
     *
     * @api
     * @return int|null
     */
    public function getSellerId()
    {
        if ($this->storage->getData('seller_id')) {
            return $this->storage->getData('seller_id');
        }
        return null;
    }

    /**
     * Retrieve seller id from current session
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getSellerId();
    }

    /**
     * Set seller id
     *
     * @param int|null $sellerId
     * @return $this
     */
    public function setId($sellerId)
    {
        return $this->setSellerId($sellerId);
    }

    /**
     * Set seller group id
     *
     * @param int|null $id
     * @return $this
     */
    public function setSellerGroupId($id)
    {
        $this->storage->setData('seller_group_id', $id);
        return $this;
    }

    /**
     * Get seller group id.
     *
     * If seller is not logged in system, 'not logged in' group id will be returned.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSellerGroupId()
    {
        if ($this->storage->getData('seller_group_id')) {
            return $this->storage->getData('seller_group_id');
        }
        if ($this->getSellerData()) {
            $sellerGroupId = $this->getSellerData()->getGroupId();
            $this->setSellerGroupId($sellerGroupId);
            return $sellerGroupId;
        }
        return Group::NOT_LOGGED_IN_ID;
    }

    /**
     * Checking seller login status
     *
     * @api
     * @return bool
     */
    public function isLoggedIn()
    {
        return (bool)$this->getSellerId()
            && $this->checkSellerId($this->getId())
            && !$this->getIsSellerEmulated();
    }

    /**
     * Check exists seller (light check)
     *
     * @param int $sellerId
     * @return bool
     */
    public function checkSellerId($sellerId)
    {
        if ($this->_isSellerIdChecked === $sellerId) {
            return true;
        }

        try {
            $this->sellerRepository->getById($sellerId);
            $this->_isSellerIdChecked = $sellerId;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sets seller as logged in
     *
     * @param Seller $seller
     * @return $this
     */
    public function setSellerAsLoggedIn($seller)
    {
        $this->regenerateId();
        $this->setSeller($seller);
        $this->_eventManager->dispatch('seller_login', ['seller' => $seller]);
        $this->_eventManager->dispatch('seller_data_object_login', ['seller' => $this->getSellerDataObject()]);
        return $this;
    }

    /**
     * Sets seller as logged in
     *
     * @param SellerData $seller
     * @return $this
     */
    public function setSellerDataAsLoggedIn($seller)
    {
        $this->regenerateId();
        $this->_httpContext->setValue(Context::CONTEXT_AUTH, true, false);
        $this->setSellerData($seller);

        $sellerModel = $this->_sellerFactory->create()->updateData($seller);

        $this->setSeller($sellerModel);

        $this->_eventManager->dispatch('seller_login', ['seller' => $sellerModel]);
        $this->_eventManager->dispatch('seller_data_object_login', ['seller' => $seller]);
        return $this;
    }

    /**
     * Authorization seller by identifier
     *
     * @api
     * @param   int $sellerId
     * @return  bool
     */
    public function loginById($sellerId)
    {
        try {
            $seller = $this->sellerRepository->getById($sellerId);
            $this->setSellerDataAsLoggedIn($seller);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Logout seller
     *
     * @api
     * @return $this
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->_eventManager->dispatch('seller_logout', ['seller' => $this->getSeller()]);
            $this->_logout();
        }
        $this->_httpContext->unsValue(Context::CONTEXT_AUTH);
        return $this;
    }

    /**
     * Authenticate controller action by login seller
     *
     * @param   bool|null $loginUrl
     * @return  bool
     */
    public function authenticate($loginUrl = null)
    {
        if ($this->isLoggedIn()) {
            return true;
        }
        $this->setBeforeAuthUrl($this->_createUrl()->getUrl('*/*/*', ['_current' => true]));
        if (isset($loginUrl)) {
            $this->response->setRedirect($loginUrl);
        } else {
            $arguments = $this->_sellerUrl->getLoginUrlParams();
            $this->response->setRedirect(
                $this->_createUrl()->getUrl(\Magento\Seller\Model\Url::ROUTE_ACCOUNT_LOGIN, $arguments)
            );
        }

        return false;
    }

    /**
     * Set auth url
     *
     * @param string $key
     * @param string $url
     * @return $this
     */
    protected function _setAuthUrl($key, $url)
    {
        $url = $this->_createUrl()->getRebuiltUrl($url);
        return $this->storage->setData($key, $url);
    }

    /**
     * Logout without dispatching event
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logout()
    {
        $this->_seller = null;
        $this->_sellerModel = null;
        $this->setSellerId(null);
        $this->setSellerGroupId($this->groupManagement->getNotLoggedInGroup()->getId());
        $this->destroy(['clear_storage' => false]);
        return $this;
    }

    /**
     * Set Before auth url
     *
     * @param string $url
     * @return $this
     */
    public function setBeforeAuthUrl($url)
    {
        return $this->_setAuthUrl('before_auth_url', $url);
    }

    /**
     * Set After auth url
     *
     * @param string $url
     * @return $this
     */
    public function setAfterAuthUrl($url)
    {
        return $this->_setAuthUrl('after_auth_url', $url);
    }

    /**
     * Reset core session hosts after resetting session ID
     *
     * @return $this
     */
    public function regenerateId()
    {
        parent::regenerateId();
        $this->_cleanHosts();
        return $this;
    }

    /**
     * Creates URL object
     *
     * @return \Magento\Framework\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }
}
