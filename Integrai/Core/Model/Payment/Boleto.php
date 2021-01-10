<?php

namespace Integrai\Core\Model\Payment;

class Boleto extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'integrai_boleto';
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canUseForMultishipping  = true;

    public function isAvailable($quote = null)
    {
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $additional_data = $data->getData('additional_data');
        $info = $this->getInfoInstance();
        if (isset($additional_data)) {
            foreach ($additional_data as $key => $value) {
                if (is_object(json_decode($value))) {
                    $value = json_decode($value);
                }

                $info->setAdditionalInformation($key, $value);
            }
        }

        return $this;
    }
}