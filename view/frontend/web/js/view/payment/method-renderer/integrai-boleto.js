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
                template: 'Integrai_Core/payment/integrai-boleto',
                redirectAfterPlaceOrder: false,
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
                $('input[name^="payment[additional_data]"], select[name^="payment[additional_data]"], textarea[name^="payment[additional_data]"]').each(function() {
                    const elem = $(this);
                    const elemName = elem.attr('name');
                    const name = elemName.match(/\w+\[(\w+)\]\[(\w+)\]/)[2];
                    const subNameMatch = elemName.match(/\w+\[(\w+)\]\[(\w+)\]\[(\w+)\]/);
                    const subName = subNameMatch ? subNameMatch[3] : null;
                    const value = elem.val();

                    if(subName){
                        Object.assign(additional_data, {
                            [name]: JSON.stringify({
                                ...JSON.parse(additional_data[name] || '{}'),
                                [subName]: value
                            }),
                        });
                    } else {
                        Object.assign(additional_data, {
                            [name]: value,
                        });
                    }
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
            },

            afterPlaceOrder: function () {
                window.location = window.checkoutConfig.integrai_success_url;
            },
        });
    }
);
