/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mage/storage',
    'Magento_Checkout/js/model/url-builder'
], function (storage, urlBuilder) {
    'use strict';

    return function (deferred, email) {
        return storage.post(
            urlBuilder.createUrl('/sellers/isEmailAvailable', {}),
            JSON.stringify({
                sellerEmail: email
            }),
            false
        ).done(function (isEmailAvailable) {
            if (isEmailAvailable) {
                deferred.resolve();
            } else {
                deferred.reject();
            }
        }).fail(function () {
            deferred.reject();
        });
    };
});
