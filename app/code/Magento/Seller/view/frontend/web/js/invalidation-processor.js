/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiElement',
    'Magento_Seller/js/seller-data'
], function (_, Element, sellerData) {
    'use strict';

    return Element.extend({
        /**
         * Initialize object
         */
        initialize: function () {
            this._super();
            this.process(sellerData);
        },

        /**
         * Process all rules in loop, each rule can invalidate some sections in seller data
         *
         * @param {Object} sellerDataObject
         */
        process: function (sellerDataObject) {
            _.each(this.invalidationRules, function (rule, ruleName) {
                _.each(rule, function (ruleArgs, rulePath) {
                    require([rulePath], function (Rule) {
                        var currentRule = new Rule(ruleArgs);

                        if (!_.isFunction(currentRule.process)) {
                            throw new Error('Rule ' + ruleName + ' should implement invalidationProcessor interface');
                        }
                        currentRule.process(sellerDataObject);
                    });
                });
            });
        }
    });
});
