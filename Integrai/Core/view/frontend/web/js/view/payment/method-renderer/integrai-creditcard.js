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
                this.loadScripts();
            },

            getCode: function() {
                return 'integrai_creditcard';
            },

            getData: function () {
                const card_hashs = {};

                $('input[name^="payment[additional_data][card_hash_"]').each(function() {
                    const elem = $(this);
                    const name = elem.attr('name').match(/\w+\[(\w+)\]\[(\w+)\]/)[2];
                    const value = elem.val();
                    Object.assign(card_hashs, {
                        [name]: value,
                    });
                });

                console.log('card_hashs', card_hashs);

                return {
                    method: this.item.method,
                    additional_data: {
                        ...card_hashs,
                    }
                };
            },

            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },

            loadScripts: function () {
                const integraiCreditcard = window.checkoutConfig.integrai_creditcard;

                const scripts = [
                    integraiCreditcard.form,
                    ...integraiCreditcard.gateways,
                ];

                scripts.forEach(function (script) {
                    let scriptElm = document.createElement('script');
                    scriptElm.src = script;
                    document.head.appendChild(scriptElm);
                });
            }
        });
    }
);
