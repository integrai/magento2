<?php

namespace Integrai\Core\Block\Adminhtml\Order\View;

class PaymentInfo extends \Magento\Backend\Block\Template
{
    protected $_helper;
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Integrai\Core\Helper\Data $helper,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
    }

    public function getPaymentResponse() {
        $order = $this->_coreRegistry->registry('sales_order');
        $paymentAdditionalInformation = $order->getPayment()->getAdditionalInformation();
        return isset($paymentAdditionalInformation['payment_response']) ? $paymentAdditionalInformation['payment_response'] : array();
    }
}