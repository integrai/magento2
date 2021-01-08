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
                template: 'Integrai_Core/payment/integrai-boleto'
            },

            getCode: function() {
                return 'integrai_boleto';
            },

            getData: function () {
                return {
                    method: this.item.method,
                    additional_data: this.getFieldsAdditionalData(),
                };
            },

            getFieldsAdditionalData: function () {
                const additional_data = {};
                $('input[name^="payment[additional_data]"]').each(function() {
                    const elem = $(this);
                    const name = elem.attr('name').match(/\w+\[(\w+)\]\[(\w+)\]/)[2];
                    const value = elem.val();
                    Object.assign(additional_data, {
                        [name]: value,
                    });
                });
                return additional_data;
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                const $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            loadScripts: function () {
                const {
                    formOptions = {},
                    scripts = [],
                } = window.checkoutConfig.integrai_boleto || {};

                window.IntegraiBoleto = {
                    ...formOptions,
                    boletoModel: window.checkoutConfig.integrai_customer
                };

                scripts.forEach(function (script) {
                    let scriptElm = document.createElement('script');
                    scriptElm.src = script;
                    document.head.appendChild(scriptElm);
                });
            }
        });
    }
);
