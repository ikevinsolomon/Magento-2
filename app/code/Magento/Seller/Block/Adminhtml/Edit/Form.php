<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Edit;

use Magento\Seller\Controller\RegistryConstants;

/**
 * Adminhtml seller edit form block
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Seller Repository.
     *
     * @var \Magento\Seller\Api\SellerRepositoryInterface
     */
    protected $_sellerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Seller\Api\SellerRepositoryInterface $sellerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Seller\Api\SellerRepositoryInterface $sellerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        array $data = []
    ) {
        $this->_sellerRepository = $sellerRepository;
        $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare the form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('seller/*/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $sellerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SELLER_ID);

        if ($sellerId) {
            $form->addField('id', 'hidden', ['name' => 'seller_id']);
            $seller = $this->_sellerRepository->getById($sellerId);
            $form->setValues(
                $this->_extensibleDataObjectConverter->toFlatArray(
                    $seller,
                    [],
                    \Magento\Seller\Api\Data\SellerInterface::class
                )
            )->addValues(
                ['seller_id' => $sellerId]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
