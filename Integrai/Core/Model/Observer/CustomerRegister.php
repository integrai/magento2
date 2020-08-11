<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class CustomerRegister implements ObserverInterface{
    private $_helper;
    private $_api;
    private $_customer;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Customer\Model\Customer $customer
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_customer = $customer;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        if ($this->_getHelper()->isEventEnabled(Events::SAVE_CUSTOMER)) {
            $customer = $this->_customer->load($observer->getData('customer_data_object')->getId());
            return $this->_getApi()->sendEvent(Events::SAVE_CUSTOMER, $customer->getData());
        }
    }
}