<?php

namespace Integrai\Core\Model;

class Order {
    private $_helper;
    private $_quote;
    private $_storeManager;
    private $_productRepository;
    private $_quoteRepository;
    private $_quoteManagement;
    private $_customerRepository;
    private $_country;

    public function __construct(
        \Integrai\Core\Helper\Data $helper,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Directory\Model\Country $country
    )
    {
        $this->_helper = $helper;
        $this->_quote = $quote;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteManagement = $quoteManagement;
        $this->_customerRepository = $customerRepository;
        $this->_country = $country;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function create($orderData, $customerId) {
        $store = $this->_storeManager->getDefaultStoreView();

        $quote = $this->_quote->create();
        $quote->setStore($store);
        $quote->setCurrency();

        $customer= $this->_customerRepository->getById($customerId);
        $quote->assignCustomer($customer);

        foreach($orderData['items'] as $item) {
            $product = $this->_productRepository->get($item['sku']);
            $product->setPrice($item['price']);
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        $billingRegion = !empty($orderData['billing_address']['address_state_code']) ? $orderData['billing_address']['address_state_code'] : $this->getRegion($orderData['billing_address']);
        $quote->getBillingAddress()->addData(array(
            'firstname' => $orderData['billing_address']['firstname'],
            'lastname' => $orderData['billing_address']['lastname'],
            'street' => array(
                $orderData['billing_address']['address_street'],
                $orderData['billing_address']['address_number'],
                $orderData['billing_address']['address_complement'],
                $orderData['billing_address']['address_neighborhood']
            ),
            'city' => $orderData['billing_address']['address_city'],
            'country_id' => 'BR',
            'region' => $billingRegion,
            'postcode' => $orderData['billing_address']['address_zipcode'],
            'telephone' => $orderData['billing_address']['telephone'],
            'save_in_address_book' => 1
        ));

        $shippingRegion = !empty($orderData['shipping_address']['address_state_code']) ? $orderData['shipping_address']['address_state_code'] : $this->getRegion($orderData['shipping_address']);
        $quote->getShippingAddress()->addData(array(
            'firstname' => $orderData['shipping_address']['firstname'],
            'lastname' => $orderData['shipping_address']['lastname'],
            'street' => array(
                $orderData['shipping_address']['address_street'],
                $orderData['shipping_address']['address_number'],
                $orderData['shipping_address']['address_complement'],
                $orderData['shipping_address']['address_neighborhood']
            ),
            'city' => $orderData['shipping_address']['address_city'],
            'country_id' => 'BR',
            'region' => $shippingRegion,
            'postcode' => $orderData['shipping_address']['address_zipcode'],
            'telephone' => $orderData['shipping_address']['telephone'],
            'save_in_address_book' => 1
        ));

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('flatrate_flatrate');

        $quote->setPaymentMethod('integrai_marketplace');
        $quote->setInventoryProcessed(false);
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        $quote->save();

        $quote->getPayment()->importData(['method' => 'integrai_marketplace']);

        $quote->collectTotals()->save();

        $shippingDescription = $orderData['order']['shipping_carrier'] . ' - ' . $orderData['order']['shipping_method'];
        $shippingPrice = $orderData['order']['shipping_amount'];

        $order = $this->_quoteManagement->submit($quote);
        $order->setEmailSent(0);

        $order->setExtOrderId($orderData['order']['external_id']);
        $order->setShippingAmount($shippingPrice);
        $order->setBaseShippingAmount($shippingPrice);
        $order->setShippingDescription($shippingDescription);
        $order->setGrandTotal($order->getSubTotal() + $shippingPrice);
        $order->getPayment()->setAdditionalInformation(array(
            "marketplace" => isset($orderData['marketplace']) ? $orderData['marketplace'] : '',
            "payments" => isset($orderData['payments']) ? $orderData['payments'] : ''
        ));
        $order->save();

        return $order->getData();
    }


    private function getRegion($address) {
        foreach ($this->_country->loadByCode('BR')->getRegions() as $region) {
            $addressState = strtolower($address['address_state']);

            if ($addressState === strtolower($region->getName()) || $addressState === strtolower($region->getDefaultName())) {
                return $region->getCode();
            }
        }

        return null;
    }
}