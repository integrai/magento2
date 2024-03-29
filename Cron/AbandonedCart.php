<?php

namespace Integrai\Core\Cron;

use \Integrai\Core\Model\Observer\Events;

class AbandonedCart
{
    private $_helper;
    private $_api;
    private $_quoteFactory;
    private $_customer;
    private $_date;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_quoteFactory = $quoteFactory;
        $this->_customer = $customer;
        $this->_date = $date;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi()
    {
        return $this->_api;
    }

    protected function _getQuoteData($item, $quote)
    {
      $newItem = new \Magento\Framework\DataObject();
      $newItem->addData($item->getData());
      $newItem->setCartId($quote->getId());
      $newItem->setProduct($item->getProduct()->getData());

      return $newItem;
    }

    protected function _getItems($quote)
    {
      $items = array();

      foreach ($quote->getAllItems() as $item) {
        if ($item->getHasChildren()) {
          foreach ($item->getChildren() as $child) {
            $items[] = $this->_getQuoteData($child, $quote);
          }
        } else {
          $items[] = $this->_getQuoteData($item, $quote);
        }
      }

      return $items;
    }

    public function execute()
    {
        try {
          if ($this->_getHelper()->isEventEnabled(Events::ABANDONED_CART)) {
              $minutes = $this->_getHelper()->getGlobalConfig('minutesAbandonedCartLifetime', 60);
              $fromDate = date('Y-m-d H:i:s', strtotime('-'.$minutes. ' minutes'));
              $toDate = date('Y-m-d H:i:s', strtotime("now"));

              $quotes = $this->_quoteFactory
                  ->create()
                  ->getCollection()
                  ->addFieldToFilter('is_active', 1)
                  ->addFieldToFilter('items_count', array('gt' => 0))
                  ->addFieldToFilter('customer_email', array('notnull' => true))
                  ->addFieldToFilter('updated_at', ['lteq' => $toDate])
                  ->addFieldToFilter('updated_at', ['gteq' => $fromDate])
                  ->load();

              if ($quotes->count() > 0) {
                  foreach ($quotes as $quote) {
                      $customer = $this->_customer->load($quote->getCustomerId());
                      $items = $this->_getItems($quote);

                      $data = new \Magento\Framework\DataObject();
                      $data->setCartId($quote->getId());
                      $data->setQuote($quote->getData());
                      $data->setItems($items);
                      $data->setQuantity(count($items));
                      $data->setCustomer($customer->getData());
                      $data->setCreatedAt($quote->getData('created_at'));

                      $this->_getApi()->sendEvent(Events::ABANDONED_CART, $data->getData());

                      if ($this->_getHelper()->isEventEnabled(Events::ABANDONED_CART_ITEM)) {
                          foreach ($items as $item) {
                              $item->setCustomer($customer->getData());
                              $this->_getApi()->sendEvent(Events::ABANDONED_CART_ITEM, $item->getData());
                          }
                      }
                  }
              }
          }
        } catch (\Throwable $e) {
          $this->_getHelper()->log('ABANDONED_CART Error', $e->getMessage());
        }
    }
}