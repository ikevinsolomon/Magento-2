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
    'underscore',
    './address-list'
], function ($, ko, _, addressList) {
    'use strict';

    var isLoggedIn = ko.observable(window.isSellerLoggedIn),
        sellerData = {};

    if (isLoggedIn()) {
        sellerData = window.sellerData;
    } else {
        sellerData = {};
    }

    return {
        sellerData: sellerData,
        sellerDetails: {},
        isLoggedIn: isLoggedIn,

        /**
         * @param {Boolean} flag
         */
        setIsLoggedIn: function (flag) {
            isLoggedIn(flag);
        },

        /**
         * @return {Array}
         */
        getBillingAddressList: function () {
            return addressList();
        },

        /**
         * @return {Array}
         */
        getShippingAddressList: function () {
            return addressList();
        },

        /**
         * @param {String} fieldName
         * @param {*} value
         */
        setDetails: function (fieldName, value) {
            if (fieldName) {
                this.sellerDetails[fieldName] = value;
            }
        },

        /**
         * @param {String} fieldName
         * @return {*}
         */
        getDetails: function (fieldName) {
            if (fieldName) {
                if (this.sellerDetails.hasOwnProperty(fieldName)) {
                    return this.sellerDetails[fieldName];
                }

                return undefined;
            }

            return this.sellerDetails;
        },

        /**
         * @param {Array} address
         * @return {Number}
         */
        addSellerAddress: function (address) {
            var fields = [
                'seller_id', 'country_id', 'street', 'company', 'telephone', 'fax', 'postcode', 'city',
                'firstname', 'lastname', 'middlename', 'prefix', 'suffix', 'vat_id', 'default_billing',
                'default_shipping'
            ],
                sellerAddress = {},
                hasAddress = 0,
                existingAddress;

            if (!this.sellerData.addresses) {
                this.sellerData.addresses = [];
            }

            sellerAddress = _.pick(address, fields);

            if (address.hasOwnProperty('region_id')) {
                sellerAddress.region = {
                    'region_id': address['region_id'],
                    region: address.region
                };
            }

            for (existingAddress in this.sellerData.addresses) {
                if (this.sellerData.addresses.hasOwnProperty(existingAddress)) {
                    if (_.isEqual(this.sellerData.addresses[existingAddress], sellerAddress)) { //eslint-disable-line
                        hasAddress = existingAddress;
                        break;
                    }
                }
            }

            if (hasAddress === 0) {
                return this.sellerData.addresses.push(sellerAddress) - 1;
            }

            return hasAddress;
        },

        /**
         * @param {*} addressId
         * @return {Boolean}
         */
        setAddressAsDefaultBilling: function (addressId) {
            if (this.sellerData.addresses[addressId]) {
                this.sellerData.addresses[addressId]['default_billing'] = 1;

                return true;
            }

            return false;
        },

        /**
         * @param {*} addressId
         * @return {Boolean}
         */
        setAddressAsDefaultShipping: function (addressId) {
            if (this.sellerData.addresses[addressId]) {
                this.sellerData.addresses[addressId]['default_shipping'] = 1;

                return true;
            }

            return false;
        }
    };
});
