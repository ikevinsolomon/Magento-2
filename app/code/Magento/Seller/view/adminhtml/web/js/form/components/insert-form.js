/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form'
], function (Insert) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                addressListing: '${ $.addressListingProvider }',
                addressModal: '${ $.addressModalProvider }'
            }
        },

        /**
         * Close modal, reload seller address listing and save seller address
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            var data;

            if (!responseData.error) {
                this.addressModal().closeModal();
                this.addressListing().reload({
                    refresh: true
                });
                data = this.externalSource().get('data');
                this.saveAddress(responseData, data);
            }
        },

        /**
         * Save seller address to seller form data source
         *
         * @param {Object} responseData
         * @param {Object} data - seller address
         */
        saveAddress: function (responseData, data) {
            data['entity_id'] = responseData.data['entity_id'];

            if (parseFloat(data['default_billing'])) {
                this.source.set('data.default_billing_address', data);
            } else if (
                parseFloat(this.source.get('data.default_billing_address')['entity_id']) === data['entity_id']
            ) {
                this.source.set('data.default_billing_address', []);
            }

            if (parseFloat(data['default_shipping'])) {
                this.source.set('data.default_shipping_address', data);
            } else if (
                parseFloat(this.source.get('data.default_shipping_address')['entity_id']) === data['entity_id']
            ) {
                this.source.set('data.default_shipping_address', []);
            }
        },

        /**
         * Event method that closes "Edit seller address" modal and refreshes grid after seller address
         * was removed through "Delete" button on the "Edit seller address" modal
         *
         * @param {String} id - seller address ID to delete
         */
        onAddressDelete: function (id) {
            this.addressModal().closeModal();
            this.addressListing().reload({
                refresh: true
            });
            this.addressListing()._delete([parseFloat(id)]);
        }
    });
});
