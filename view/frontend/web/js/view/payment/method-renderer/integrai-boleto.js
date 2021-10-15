/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
    ],
    function (Component, $, selectPaymentMethodAction, checkoutData) {
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

                const $form = $('#co-shipping-form');

                console.log('$form', $form);

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

            selectPaymentMethod: function () {
                const $form = $('#co-shipping-form');

                if ($form.length > 0) {
                    const formData = $form.serializeArray();

                    const props = {
                        companyName: 'company',
                        addressStreet: 'street[0]',
                        addressNumber: 'street[1]',
                        addressCity: 'city',
                        addressState: 'region_id',
                        addressZipCode: 'postcode',
                        docNumber: 'vat_id',
                        name: 'firstname',
                        lastName: 'lastname',
                    };

                    Object.keys(props).forEach((key) => {
                        const prop = props[key];
                        let value = (formData.find(item => item.name === prop) || {}).value;

                        if (key === 'addressState' && Array.isArray(window.checkoutConfig.integrai_regions)) {
                            value = window.checkoutConfig.integrai_regions.find(region => region.region_id === value).code;
                        }

                        window.checkoutConfig.integrai_customer[key] = value;
                    });

                    const event = new Event('modelChangeBoleto');
                    event.data = window.checkoutConfig.integrai_customer;
                    document.dispatchEvent(event);
                }

                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                return true;
            },

            afterPlaceOrder: function () {
                window.location = window.checkoutConfig.integrai_success_url;
            },
        });
    }
);
