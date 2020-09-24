<?php

namespace Integrai\Core\Cron;

use \Integrai\Core\Model\Observer\Events;

class AbandonedCart
{
    private $_helper;
    private $_api;
    private $_quoteFactory;
    private $_customer;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\Customer $customer
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_quoteFactory = $quoteFactory;
        $this->_customer = $customer;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    public function execute()
    {
        if ($this->_getHelper()->isEventEnabled(Events::ABANDONED_CART)) {
            $minutes = $this->_getHelper()->getGlobalConfig('minutes_abandoned_cart_lifetime', 60);
            $fromDate = date('Y-m-d H:i:s', strtotime('-'.$minutes. ' minutes'));
            $toDate = date('Y-m-d H:i:s', strtotime("now"));

            $quotes = $this->_quoteFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('items_count', array('gt' => 0))
                ->addFieldToFilter('customer_email', array('notnull' => true))
                ->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                ->load();

            if($quotes->count() > 0){
                $abandonedCart = array();
                foreach ($quotes as $quote) {
                    $customer = $this->_customer->load($quote->getCustomerId());

                    $data = new \Magento\Framework\DataObject();
                    $data->setQuote($quote->getData());
                    $items = array_map(function($item) {
                        $newItem = new \Magento\Framework\DataObject();
                        $newItem->addData($item->getData());
                        $newItem->setProduct($item->getProduct()->getData());
                        return $newItem->getData();
                    }, $quote->getAllItems());
                    $data->setItems($items);
                    $data->setCustomer($customer->getData());
                    $abandonedCart[] = $data->getData();
                }

                return $this->_getApi()->sendEvent(Events::ABANDONED_CART, $abandonedCart);
            }
        }
    }
}