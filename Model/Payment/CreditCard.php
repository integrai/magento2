<?php

namespace Integrai\Core\Model\Payment;

class CreditCard extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code                    = 'integrai_creditcard';
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canUseForMultishipping  = false;

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
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