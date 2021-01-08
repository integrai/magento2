<?php

namespace Integrai\Core\Model;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {

    private $_helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $_regionFactory;
    protected $_addressFactory;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\RegionFactory $regionFactory
    )
    {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_regionFactory = $regionFactory;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function getConfig(){
        $quote = $this->_checkoutSession->getQuote();
        $billing_address = $quote->getBillingAddress()->getData();
        $street = $quote->getBillingAddress()->getStreet();
        $regionCode = $this->_regionFactory->create()->load($billing_address['region_id'])->getCode();

        $customerSession = $this->_customerSession->getCustomer();

        if(!empty($customerSession->getId())){
            $customerInfo = array(
                "name" => $customerSession->getFirstname(),
                "lastName" => $customerSession->getLastname(),
                "docNumber" => $customerSession->getTaxvat(),
            );
        } else {
            $customerInfo = array(
                "name" => $quote->getCustomerFirstname() ? $quote->getCustomerFirstname() : $billing_address['firstname'],
                "lastName" => $quote->getCustomerLastname() ? $quote->getCustomerLastname() : $billing_address['lastname'],
                "docNumber" => $quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : $billing_address['vat_id'],
            );
        }

        return [
            'integrai_boleto' => $this->_getHelper()->getConfigTable('PAYMENT_BOLETO'),
            'integrai_creditcard' => $this->_getHelper()->getConfigTable('PAYMENT_CREDITCARD'),
            'integrai_amount' => (float)$quote->getBaseGrandTotal(),
            'integrai_customer' => array_merge($customerInfo, array(
                "companyName" => $billing_address['company'],
                "addressStreet" => isset($street) ? $street[0] : "",
                "addressNumber" => isset($street) ? $street[1] : "",
                "addressCity" => $billing_address['city'],
                "addressState" => $regionCode,
                "addressZipCode" => $billing_address['postcode'],
            )),
        ];
    }
}