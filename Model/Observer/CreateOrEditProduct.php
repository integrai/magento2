<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class CreateOrEditProduct implements ObserverInterface{
    private $_helper;
    private $_api;
    private $_customer;

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

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $event = empty($product->getOrigData('sku')) ? Events::CREATE_PRODUCT : Events::UPDATE_PRODUCT;

        if ($this->_getHelper()->isEventEnabled($event)) {
            return $this->_getApi()->sendEvent($event, $product->getData());
        }
    }
}