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
        $info->setAdditionalInformation($additional_data);
        return $this;
    }
}