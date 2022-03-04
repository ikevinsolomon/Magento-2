/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiClass'
], function (Element) {
    'use strict';

    return Element.extend({

        defaults: {
            scopeConfig: {}
        },

        /**
         * Takes website id from current seller data and compare it with current website id
         * If seller belongs to another scope, we need to invalidate current section
         *
         * @param {Object} sellerData
         */
        process: function (sellerData) {
            var seller = sellerData.get('seller');

            if (this.scopeConfig && seller() &&
                ~~seller().websiteId !== ~~this.scopeConfig.websiteId && ~~seller().websiteId !== 0) {
                sellerData.reload(['seller']);
            }
        }
    });
});
