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
            $customer = $this->_customer->load($order->getCustomerId())->getData();

            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            $billing = $order->getBillingAddress()->getData();
            $billing_street = $order->getBillingAddress()->getStreet();
            $billing['street_1'] = isset($billing_street) ? $billing_street[0] : "";
            $billing['street_2'] = isset($billing_street) ? $billing_street[1] : "";
            $billing['street_3'] = isset($billing_street) ? $billing_street[2] : "";
            $billing['street_4'] = isset($billing_street) ? $billing_street[3] : "";
            $billing['region_code'] = $order->getBillingAddress()->getRegionCode();

            $shipping = $order->getShippingAddress()->getData();
            $shipping_street = $order->getBillingAddress()->getStreet();
            $shipping['street_1'] = isset($shipping_street) ? $shipping_street[0] : "";
            $shipping['street_2'] = isset($shipping_street) ? $shipping_street[1] : "";
            $shipping['street_3'] = isset($shipping_street) ? $shipping_street[2] : "";
            $shipping['street_4'] = isset($shipping_street) ? $shipping_street[3] : "";
            $shipping['region_code'] = $order->getShippingAddress()->getRegionCode();

            $items = array();
            foreach ($order->getAllVisibleItems() as $item) {
                $items[] = $item->getData();
            }

            $payment = $order->getPayment()->getData();
            $card_hashs = $payment['additional_information']['card_hashs'];
            if(isset($card_hashs)) {
                $payment['additional_information']['card_hashs'] = json_decode($card_hashs);
            }

            $this->_getHelper()->log('payment', $payment);

            $data = new \Magento\Framework\DataObject();
            $data->setOrder($order->getData());
            $data->setCustomer($customer);
            $data->setBillingAddress($billing);
            $data->setShippingAddress($shipping);
            $data->setPayment($payment);
            $data->setItems($items);
            $data->setShippingMethod($order->getShippingMethod());

            return $this->_getApi()->sendEvent(Events::NEW_ORDER, $data->getData());
        }
    }
}