<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Seller\Block\Adminhtml\Form\Element\Address;

/**
 * Seller Address Widget Form Image Element Block
 */
class Image extends \Magento\Seller\Block\Adminhtml\Form\Element\Image
{
    /**
     * @inheritdoc
     */
    protected function _getPreviewUrl()
    {
        return $this->_adminhtmlData->getUrl(
            'seller/address/viewfile',
            ['file' => $this->urlEncoder->encode($this->getValue())]
        );
    }
}
