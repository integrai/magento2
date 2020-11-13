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
}