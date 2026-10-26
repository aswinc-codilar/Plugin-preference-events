define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Codilar_MinicartCount/minicart/count-info'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');

            return this;
        }
    });
});
