<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Group\Edit;

use Magento\Seller\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Seller\Controller\RegistryConstants;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Adminhtml seller groups edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Seller
     */
    protected $_taxSeller;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var \Magento\Seller\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Seller\Api\Data\GroupInterfaceFactory
     */
    protected $groupDataFactory;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\TaxClass\Source\Seller $taxSeller
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Seller\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Seller\Api\Data\GroupInterfaceFactory $groupDataFactory
     * @param array $data
     * @param SystemStore|null $systemStore
     * @param GroupExcludedWebsiteRepositoryInterface|null $groupExcludedWebsiteRepository
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\TaxClass\Source\Seller $taxSeller,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Seller\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Seller\Api\Data\GroupInterfaceFactory $groupDataFactory,
        array $data = [],
        SystemStore $systemStore = null,
        GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository = null
    ) {
        $this->_taxSeller = $taxSeller;
        $this->_taxHelper = $taxHelper;
        $this->_groupRepository = $groupRepository;
        $this->groupDataFactory = $groupDataFactory;
        $this->systemStore = $systemStore ?: ObjectManager::getInstance()->get(SystemStore::class);
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository
            ?: ObjectManager::getInstance()->get(GroupExcludedWebsiteRepositoryInterface::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form for render
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $groupId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);
        /** @var \Magento\Seller\Api\Data\GroupInterface $sellerGroup */
        $sellerGroupExcludedWebsites = [];
        if ($groupId === null) {
            $sellerGroup = $this->groupDataFactory->create();
            $defaultSellerTaxClass = $this->_taxHelper->getDefaultSellerTaxClass();
        } else {
            $sellerGroup = $this->_groupRepository->getById($groupId);
            $defaultSellerTaxClass = $sellerGroup->getTaxClassId();
            $sellerGroupExcludedWebsites = $this->groupExcludedWebsiteRepository->getSellerGroupExcludedWebsites(
                $groupId
            );
        }

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Group Information')]);

        $validateClass = sprintf(
            'required-entry validate-length maximum-length-%d',
            \Magento\Seller\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
        );
        $name = $fieldset->addField(
            'seller_group_code',
            'text',
            [
                'name' => 'code',
                'label' => __('Group Name'),
                'title' => __('Group Name'),
                'note' => __(
                    'Maximum length must be less then %1 characters.',
                    \Magento\Seller\Model\GroupManagement::GROUP_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        if ($sellerGroup->getId() == 0 && $sellerGroup->getCode()) {
            $name->setDisabled(true);
        }

        $fieldset->addField(
            'tax_class_id',
            'select',
            [
                'name' => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_taxSeller->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'seller_group_excluded_website_ids',
            'multiselect',
            [
                'name' => 'seller_group_excluded_websites',
                'label' => __('Excluded Website(s)'),
                'title' => __('Excluded Website(s)'),
                'required' => false,
                'can_be_empty' => true,
                'values' => $this->systemStore->getWebsiteValuesForForm(),
                'note' => __('Select websites you want to exclude from this seller group.')
            ]
        );

        if ($sellerGroup->getId() !== null) {
            // If edit add id
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $sellerGroup->getId()]);
        }

        if ($this->_backendSession->getSellerGroupData()) {
            $form->addValues($this->_backendSession->getSellerGroupData());
            $this->_backendSession->setSellerGroupData(null);
        } else {
            // TODO: need to figure out how the DATA can work with forms
            $form->addValues(
                [
                    'id' => $sellerGroup->getId(),
                    'seller_group_code' => $sellerGroup->getCode(),
                    'tax_class_id' => $defaultSellerTaxClass,
                    'seller_group_excluded_website_ids' => $sellerGroupExcludedWebsites
                ]
            );
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('seller/*/save'));
        $form->setMethod('post');
        $this->setForm($form);
    }
}
