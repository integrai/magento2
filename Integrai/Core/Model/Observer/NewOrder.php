<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class NewOrder implements ObserverInterface{
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
        if ($this->_getHelper()->isEventEnabled(Events::NEW_ORDER)) {
            $this->_getHelper()->log('order');

            $order = $observer->getEvent()->getOrder();
            $customer = $this->_customer->load($order->getCustomerId());

            $data = new \Magento\Framework\DataObject();
            $data->setOrder($order->getData());
            $data->setCustomer($customer->getData());
            $data->setBillingAddress($order->getBillingAddress()->getData());
            $data->setShippingAddress($order->getShippingAddress()->getData());
            $data->setPayment($order->getPayment()->getData());
            $data->setShippingMethod($order->getShippingMethod());

            return $this->_getApi()->sendEvent(Events::NEW_ORDER, $data->getData());
        }
    }
}