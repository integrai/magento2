<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class CancelOrder implements ObserverInterface{
    private $_helper;
    private $_api;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        if ($this->_getHelper()->isEventEnabled(Events::UPDATE_ORDER)) {
            $order = $observer->getEvent()->getOrder();

            return $this->_getApi()->sendEvent(Events::UPDATE_ORDER, $order->getData());
        }
    }
}