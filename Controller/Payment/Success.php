<?php

namespace Integrai\Core\Controller\Payment;

class Success extends \Magento\Framework\App\Action\Action
{
    protected $_request;
    protected $_pageFactory;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api
    )
    {
        $this->_request = $request;
        $this->_pageFactory = $pageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_api = $api;
        return parent::__construct($context);
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi(){
        return $this->_api;
    }

    public function execute()
    {
        $this->_view->loadLayout(['default', 'integrai_success']);
        $this->_view->renderLayout();
    }
}