/**
* Copyright © Magento, Inc. All rights reserved.
* See COPYING.txt for license details.
*/

define([
    'uiComponent',
    'Magento_Seller/js/seller-data'
], function (Component, sellerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.seller = sellerData.get('seller');
        }
    });
});
