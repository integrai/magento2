/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Integrai_Core/payment/integrai-creditcard'
            },

            initialize: function() {
                this._super();
            },

            getCode: function() {
                return 'integrai_creditcard';
            },

            getData: function () {
                const card_hashs = {};

                this.getFieldsHash().each(function() {
                    const elem = $(this);
                    const name = elem.attr('name').match(/\w+\[(\w+)\]\[(\w+)\]/)[2];
                    const value = elem.val();
                    Object.assign(card_hashs, {
                        [name]: value,
                    });
                });

                return {
                    method: this.item.method,
                    additional_data: {
                        ...card_hashs,
                    }
                };
            },

            getFieldsHash: function () {
                return $('input[name^="payment[additional_data][card_hash_"]');
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                const $form = $('#' + this.getCode() + '-form');
                return this.hasFieldsHash() && $form.validation() && $form.validation('isValid');
            },

            hasFieldsHash: function () {
                let totalHash = 0;

                this.getFieldsHash().each(function() {
                    if ($(this).val() !== '') {
                        totalHash++;
                    }
                });

                return totalHash > 0;
            },

            loadScripts: function () {
                const {
                    formOptions = {},
                    scripts = [],
                } = window.checkoutConfig.integrai_creditcard || {};

                window.Integrai = formOptions;

                scripts.forEach(function (script) {
                    let scriptElm = document.createElement('script');
                    scriptElm.src = script;
                    document.head.appendChild(scriptElm);
                });
            }
        });
    }
);
