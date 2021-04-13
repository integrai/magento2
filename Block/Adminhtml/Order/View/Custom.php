<?php

namespace Integrai\Core\Block\Adminhtml\Order\View;

class Custom extends \Magento\Backend\Block\Template
{
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Integrai\Core\Helper\Data $helper,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    public function getDangerAlert() {
        return $this->_helper->getConfigTable('GLOBAL', 'dangerAlert', null);
    }
}