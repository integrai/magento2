<?php

namespace Integrai\Core\Cron;

use \Integrai\Core\Model\Observer\Events;

class CustomerBirthday
{
    private $_helper;
    private $_api;
    private $_customerFactory;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_customerFactory = $customerFactory;
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
        if ($this->_getHelper()->isEventEnabled(Events::CUSTOMER_BIRTHDAY)) {
            $customers = $this->_customerFactory
                ->create()
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('dob', array('like' => '%'.date("m").'-'.date("d")))
                ->load();

            foreach ($customers as $customer) {
                $this->_getApi()->sendEvent(Events::CUSTOMER_BIRTHDAY, $customer->getData());
            }
        }
    }
}