/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'integrai_boleto',
                component: 'Integrai_Core/js/view/payment/method-renderer/integrai-boleto'
            },
            {
                type: 'integrai_creditcard',
                component: 'Integrai_Core/js/view/payment/method-renderer/integrai-creditcard'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);