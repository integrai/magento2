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

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $isNewCustomer = $observer->getData('orig_customer_data_object') === null;
        $event = $isNewCustomer ? Events::CREATE_CUSTOMER : Events::UPDATE_CUSTOMER;

        if ($this->_getHelper()->isEventEnabled($event)) {
            $customer = $this->_customer->load($observer->getData('customer_data_object')->getId());
            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            return $this->_getApi()->sendEvent($event, $customer->getData());
        }
    }
}