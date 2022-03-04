/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'ko',
    './seller/address'
], function ($, ko, Address) {
    'use strict';

    var isLoggedIn = ko.observable(window.isSellerLoggedIn);

    return {
        /**
         * @return {Array}
         */
        getAddressItems: function () {
            var items = [],
                sellerData = window.sellerData;

            if (isLoggedIn()) {
                if (Object.keys(sellerData).length) {
                    $.each(sellerData.addresses, function (key, item) {
                        items.push(new Address(item));
                    });
                }
            }

            return items;
        }
    };
});
