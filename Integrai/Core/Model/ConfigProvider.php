<?php

namespace Integrai\Core\Model;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {

    private $_helper;

    public function __construct(\Integrai\Core\Helper\Data $helper)
    {
        $this->_helper = $helper;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function getConfig(){
        return [
            'integrai_boleto' => $this->_getHelper()->getConfigTable('PAYMENT_BOLETO', null, null, false),
            'integrai_creditcard' => $this->_getHelper()->getConfigTable('PAYMENT_CREDITCARD'),
        ];
    }
}