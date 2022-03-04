<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Seller\Block\Adminhtml\Sales\Order\Address\Form\Renderer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * VAT ID element renderer
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Vat extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Validate button block
     *
     * @var null|\Magento\Backend\Block\Widget\Button
     */
    protected $_validateButton = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Seller::sales/order/create/address/form/renderer/vat.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Retrieve validate button block
     *
     * @return \Magento\Backend\Block\Widget\Button
     */
    public function getValidateButton()
    {
        if ($this->_validateButton === null) {
            /** @var $form \Magento\Framework\Data\Form */
            $form = $this->_element->getForm();

            $vatElementId = $this->_element->getHtmlId();

            $countryElementId = $form->getElement('country_id')->getHtmlId();
            $validateUrl = $this->_urlBuilder->getUrl('seller/system_config_validatevat/validateAdvanced');

            $groupMessage = __(
                'The seller is now assigned to Seller Group %s.'
            ) . ' ' . __(
                'Would you like to change the Seller Group for this order?'
            );

            $vatValidateOptions = $this->_jsonEncoder->encode(
                [
                    'vatElementId' => $vatElementId,
                    'countryElementId' => $countryElementId,
                    'groupIdHtmlId' => 'group_id',
                    'validateUrl' => $validateUrl,
                    'vatValidMessage' => __('The VAT ID is valid.'),
                    'vatInvalidMessage' => __('The VAT ID entered (%s) is not a valid VAT ID.'),
                    'vatValidAndGroupValidMessage' => __(
                        'The VAT ID is valid. The current Seller Group will be used.'
                    ),
                    'vatValidAndGroupInvalidMessage' => __(
                        'The VAT ID is valid but no Seller Group is assigned for it.'
                    ),
                    'vatValidAndGroupChangeMessage' => __(
                        'Based on the VAT ID, the seller belongs to the Seller Group %s.'
                    ) . "\n" . $groupMessage,
                    'vatValidationFailedMessage' => __(
                        'Something went wrong while validating the VAT ID.'
                    ),
                    'vatSellerGroupMessage' => __(
                        'The seller would belong to Seller Group %s.'
                    ),
                    'vatGroupErrorMessage' => __('There was an error detecting Seller Group.'),
                ]
            );

            $optionsVarName = $this->getJsVariablePrefix() . 'VatParameters';
            $scriptString = 'var ' . $optionsVarName . ' = ' . $vatValidateOptions . ';';
            $beforeHtml = $this->secureRenderer->renderTag('script', [], $scriptString, false);
            $this->_validateButton = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Widget\Button::class
            )->setData(
                [
                    'label' => __('Validate VAT Number'),
                    'before_html' => $beforeHtml,
                    'onclick' => 'order.validateVat(' . $optionsVarName . ')',
                ]
            );
        }

        return $this->_validateButton;
    }
}
