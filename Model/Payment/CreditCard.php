<?php

namespace Integrai\Core\Model\Payment;

class CreditCard extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code                    = 'integrai_creditcard';
    protected $_isGateway               = true;
    protected $_canCapture              = true;
    protected $_canUseForMultishipping  = false;

    private $_helper;

    const NEW_ORDER = 'NEW_ORDER';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Integrai\Core\Helper\Data $helper
    )
    {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, null, null, []);
        $this->_helper = $helper;
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $formOptions = $this->_getHelper()->getConfigTable('PAYMENT_CREDITCARD', 'formOptions', array());
        $gateways = isset($formOptions) && is_array($formOptions) && isset($formOptions['gateways']) ? $formOptions['gateways'] : array();
        return $this->_getHelper()->isEventEnabled(self::NEW_ORDER) && count($gateways) > 0;
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