<?php

namespace Integrai\Core\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Integrai\Core\Model\Observer\Events;

class DeleteProduct implements ObserverInterface{
    private $_helper;
    private $_api;
    private $_attributeFactory;
    private $_productModel;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Catalog\Model\Product $productModel
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_productModel = $productModel;
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

        if ($this->_getHelper()->isEventEnabled(Events::DELETE_PRODUCT)) {
            return $this->_getApi()->sendEvent(Events::DELETE_PRODUCT, $product->getData());
        }
    }
}