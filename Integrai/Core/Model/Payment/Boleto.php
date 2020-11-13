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
}