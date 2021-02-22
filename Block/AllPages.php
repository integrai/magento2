<?php

namespace Integrai\Core\Block;

class AllPages extends \Magento\Framework\View\Element\Template
{
    protected $_helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Integrai\Core\Helper\Data $helper,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    public function getIntegraiConfigs() {
        return $this->_helper->getConfigTable('SCRIPTS');
    }
}