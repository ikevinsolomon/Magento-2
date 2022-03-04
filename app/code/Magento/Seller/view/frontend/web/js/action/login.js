/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Seller/js/seller-data',
    'mage/translate'
], function ($, storage, globalMessageList, sellerData, $t) {
    'use strict';

    var callbacks = [],

        /**
         * @param {Object} loginData
         * @param {String} redirectUrl
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (loginData, redirectUrl, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;

            return storage.post(
                'seller/ajax/login',
                JSON.stringify(loginData),
                isGlobal
            ).done(function (response) {
                if (response.errors) {
                    messageContainer.addErrorMessage(response);
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                } else {
                    callbacks.forEach(function (callback) {
                        callback(loginData);
                    });
                    sellerData.invalidate(['seller']);

                    if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        location.reload();
                    }
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not authenticate. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(loginData);
                });
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerLoginCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
