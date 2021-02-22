<?php

namespace Integrai\Core\Block;

class PageSuccess extends \Magento\Sales\Block\Order\Totals
{
    protected $checkoutSession;
    protected $customerSession;
    protected $_orderFactory;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Integrai\Core\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_helper = $helper;
    }

    public function getOrder()
    {
        return $this->_order = $this->_orderFactory->create()->loadByIncrementId(
            $this->checkoutSession->getLastRealOrderId());
    }

    /**
     * Return a url to go to order detail page
     *
     * @return string
     */
    public function getOrderUrl()
    {
        $params = ['order_id' => $this->checkoutSession->getLastRealOrder()->getId()];
        $url = $this->_urlBuilder->getUrl('sales/order/view', $params);

        return $url;
    }

    public function getIntegraiConfigs() {
        return $this->_helper->getConfigTable('PAYMENT_SUCCESS');
    }
}