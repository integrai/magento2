<?php

namespace Integrai\Core\Model\Observer;

use Integrai\Core\Model\Observer\Events;
use Magento\Framework\Event\ObserverInterface;

class NewOrder implements ObserverInterface{
    private $_helper;
    private $_api;
    private $_customer;
    private $_checkoutSession;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_customer = $customer;
        $this->_checkoutSession = $checkoutSession;
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
            $order = $observer->getEvent()->getOrder();
            $order->setCreatedAt(date(DATE_ATOM));
            $customer = $this->getCustomerInfo($order);

            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            $billing = array();
            if ($order->getBillingAddress()) {
                $billing = $this->getAddress($billing, $order->getBillingAddress());
            }

            $shipping = array();
            if ($order->getShippingAddress()) {
                $shipping = $this->getAddress($shipping, $order->getShippingAddress());
            }

            $items = array();
            foreach ($order->getAllVisibleItems() as $item) {
                if (!$item->getHasChildren()) {
                    $items[] = $item->getData();
                }
            }

            $order->setTotalItems(count($items));

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

            $this->_getApi()->sendEvent(Events::NEW_ORDER, $data->getData());

            if ($this->_getHelper()->isEventEnabled(Events::NEW_ORDER_ITEM)) {
                foreach ($items as $item) {
                    $item['order_id'] = $order->getIncrementId();
                    $item['customer'] = $customer;

                    $this->_getApi()->sendEvent(Events::NEW_ORDER_ITEM, $item);
                }
            }
        }
    }

    protected function getAddress($oldAddress, $orderAddress) {
        $address = $orderAddress->getData();
        $address_street = $orderAddress->getStreet();
        $address['street_1'] = isset($address_street[0]) ? $address_street[0] : "";
        $address['street_2'] = isset($address_street[1]) ? $address_street[1] : "";
        $address['street_3'] = isset($address_street[2]) ? $address_street[2] : "";
        $address['street_4'] = isset($address_street[3]) ? $address_street[3] : "";
        $address['region_code'] = $orderAddress->getRegionCode();
        return array_merge($oldAddress, $address);
    }

    protected function getCustomerInfo($order) {
        $customer = new \Magento\Framework\DataObject();

        if ($order->getCustomerId()) {
            $customer->setData($this->_customer->load($order->getCustomerId())->getData());
        } else {
            $quote = $this->_checkoutSession->getQuote();
            $billing = $order->getBillingAddress()->getData();

            $customer->setEntityId($quote->getCustomerId());
            $customer->setGroupId($quote->getCustomerGroupId());
            $customer->setFirstname($quote->getCustomerFirstname() ? $quote->getCustomerFirstname() : $billing['firstname']);
            $customer->setLastname($quote->getCustomerLastname() ? $quote->getCustomerLastname() : $billing['lastname']);
            $customer->setTaxvat($quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : $billing['vat_id']);
            $customer->setEmail($quote->getCustomerEmail());
            $customer->setDob($quote->getCustomerDob());
            $customer->setGender($quote->getCustomerGender());
            $customer->setCreatedAt(date(DATE_ATOM));
        }

        return $customer->getData();
    }
}