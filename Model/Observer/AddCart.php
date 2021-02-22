<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class AddCart implements ObserverInterface{
    private $_helper;
    private $_api;
    private $_customerSession;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_customerSession = $customerSession;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        if ($this->_getHelper()->isEventEnabled(Events::ADD_PRODUCT_CART) && $this->_customerSession->isLoggedIn()) {
            $quote = $observer->getData('quote_item')->getData();

            $data = new \Magento\Framework\DataObject();
            $data->setCustomer($this->_customerSession->getCustomer()->getData());
            $data->setItem($quote);

            return $this->_getApi()->sendEvent(Events::ADD_PRODUCT_CART, $data->getData());
        }
    }
}