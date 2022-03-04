<?php
/**
 * Seller Form Element Factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Model\Metadata;

class FormFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create Form
     *
     * @param string $entityType
     * @param string $formCode
     * @param array $attributeValues Key is attribute code.
     * @param bool $isAjax
     * @param bool $ignoreInvisible
     * @param array $filterAttributes
     * @return \Magento\Seller\Model\Metadata\Form
     */
    public function create(
        $entityType,
        $formCode,
        array $attributeValues = [],
        $isAjax = false,
        $ignoreInvisible = Form::IGNORE_INVISIBLE,
        $filterAttributes = []
    ) {
        $params = [
            'entityType' => $entityType,
            'formCode' => $formCode,
            'attributeValues' => $attributeValues,
            'ignoreInvisible' => $ignoreInvisible,
            'filterAttributes' => $filterAttributes,
            'isAjax' => $isAjax,
        ];
        return $this->_objectManager->create(\Magento\Seller\Model\Metadata\Form::class, $params);
    }
}
