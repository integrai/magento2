<?php

namespace Integrai\Core\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Carrier
    extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline
    implements \Magento\Shipping\Model\Carrier\CarrierInterface {

    protected $_code = 'integrai_shipping';

    protected function _getHelper()
    {
        return new \Integrai\Core\Helper\Data();
    }

    protected function _getApi()
    {
        return new \Integrai\Core\Model\Api();
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->setRequest($request);
    }


    public function collectRates(RateRequest $request){
        try{
            $params = $this->prepareParamsRequest($request);
            $services = $this->_getApi()->request('/shipping/quote', 'POST', $params);

            $result = \Magento\Shipping\Model\Rate\ResultFactory::create();
            foreach ($services as $service) {
                $result->append($this->transformRate($service));
            }
            return $result;
        } catch (Exception $e) {
            $error = \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory::create();
            $data  = [
                'carrier'       => $this->_code,
                'carrier_title' => $this->_getHelper()->getCarrierConfig('title'),
                'error_message' => $e->getMessage(),
            ];
            $error->setData($data);
            return $error;
        }
    }

    private function prepareParamsRequest(RateRequest $request) {
        return array(
            "destination_zipcode" => $request->getDestPostcode(),
            "cart_total_price" => $request->getPackageValue(),
            "cart_total_quantity" => $request->getPackageQty(),
            "cart_total_weight" => $request->getPackageWeight(),
            "cart_total_height" => $request->getPackageHeight(),
            "cart_total_width" => $request->getPackageWidth(),
            "cart_total_length" => $request->getPackageDepth(),
            "items" => $this->prepareItems($request->getAllItems()),
        );
    }

    private function prepareItems(array $items)  {
        $packageItems = array();

        foreach ($items as $item) {
            if (!$this->validePackageItem($item)) {
                continue;
            }

            $attribute_width   = $this->_getHelper()->getConfigTable('SHIPPING', 'attribute_width');
            $attribute_height  = $this->_getHelper()->getConfigTable('SHIPPING', 'attribute_height');
            $attribute_length  = $this->_getHelper()->getConfigTable('SHIPPING', 'attribute_length');
            $width_default     = $this->_getHelper()->getConfigTable('SHIPPING', 'width_default');
            $height_default    = $this->_getHelper()->getConfigTable('SHIPPING', 'height_default');
            $length_default    = $this->_getHelper()->getConfigTable('SHIPPING', 'length_default');

            /** @var \Magento\Quote\Model\Quote|Item $item */

            $width  = $this->extractData($item, $attribute_width) ?: $width_default;
            $height = $this->extractData($item, $attribute_height) ?: $height_default;
            $length = $this->extractData($item, $attribute_length) ?: $length_default;

            $packageItems[] = (object) array(
                "weight" => (float) $item->getWeight(),
                "width" =>  (float) $width,
                "height" => (float) $height,
                "length" => (float) $length,
                "quantity" => (int) max(1, $item->getQty()),
                "sku" => (string) $item->getSku(),
                "unit_price" => (float) $item->getBasePrice(),
                "product" => (object) $item->getProduct()->getData(),
            );
        }

        return $packageItems;
    }

    private function validePackageItem($item) {
        /** @var \Magento\Quote\Model\Quote|Item $item */
        if ($item->getProduct()->isComposite()) {
            return false;
        }

        if ($item->getProduct()->isVirtual()) {
            return false;
        }

        return true;
    }

    private function extractData($item, $key)
    {
        /** @var \Magento\Quote\Model\Quote|Item $item */
        if ($item->getData($key)) {
            return $item->getData($key);
        }

        if ($item->getProduct()->getData($key)) {
            return $item->getProduct()->getData($key);
        }

        $value = Mage::getResourceSingleton('catalog/product')->getAttributeRawValue(
            $item->getProductId(),
            $key,
            $item->getProduct()->getStore()
        );

        return $value ?: null;
    }

    protected function transformRate($service)
    {
        $rate = $this->_rateMethodFactory->create();

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($service['carrierTitle']);
        $rate->setMethod($service['methodCode']);
        $rate->setMethodTitle($service['methodTitle']);
        $rate->setMethodDescription($service['methodDescription']);
        $rate->setPrice($service['price']);
        $rate->setCost($service['cost']);

        return $rate;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('title')];
    }

    public function isTrackingAvailable(){
        return true;
    }
}