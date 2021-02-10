<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class AfterSaveOrder implements ObserverInterface{
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
        if ($this->_getHelper()->isEventEnabled(Events::SAVE_ORDER)) {
            $order = $observer->getEvent()->getOrder();
            $customer = $this->_customer->load($order->getCustomerId())->getData();

            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            $billing = $order->getBillingAddress()->getData();
            $billing_street = $order->getBillingAddress()->getStreet();
            $billing['street_1'] = isset($billing_street[0]) ? $billing_street[0] : "";
            $billing['street_2'] = isset($billing_street[1]) ? $billing_street[1] : "";
            $billing['street_3'] = isset($billing_street[2]) ? $billing_street[2] : "";
            $billing['street_4'] = isset($billing_street[3]) ? $billing_street[3] : "";
            $billing['region_code'] = $order->getBillingAddress()->getRegionCode();

            $shipping = $order->getShippingAddress()->getData();
            $shipping_street = $order->getBillingAddress()->getStreet();
            $shipping['street_1'] = isset($shipping_street[0]) ? $shipping_street[0] : "";
            $shipping['street_2'] = isset($shipping_street[1]) ? $shipping_street[1] : "";
            $shipping['street_3'] = isset($shipping_street[2]) ? $shipping_street[2] : "";
            $shipping['street_4'] = isset($shipping_street[3]) ? $shipping_street[3] : "";
            $shipping['region_code'] = $order->getShippingAddress()->getRegionCode();

            $items = array();
            foreach ($order->getAllItems() as $item) {
                $items[] = $item->getData();
            }

            $payment = $order->getPayment()->getData();

            $additional_information = $payment['additional_information'];
            if (isset($additional_information)) {
                foreach ($additional_information as $key => $value) {
                    if (is_string($value) && is_object(json_decode($value))) {
                        $payment['additional_information'][$key] = json_decode($value);
                    }
                }
            }

            $data = new \Magento\Framework\DataObject();
            $data->setOrder($order->getData());
            $data->setCustomer($customer);
            $data->setBillingAddress($billing);
            $data->setShippingAddress($shipping);
            $data->setPayment($payment);
            $data->setItems($items);
            $data->setShippingMethod($order->getShippingMethod());

            return $this->_getApi()->sendEvent(Events::SAVE_ORDER, $data->getData());
        }
    }
}