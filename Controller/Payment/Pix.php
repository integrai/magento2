<?php

namespace Integrai\Core\Controller\Payment;

class Pix extends \Magento\Framework\App\Action\Action
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
        try{
            $order_id = trim($this->_request->getParam('order_id'));

            $this->_getHelper()->log('Buscando url do pix do pedido: ', $order_id);

            if (!$order_id) {
                throw new \Exception('Informe o ID do pedido');
            }

            $response = $this->_getApi()->request('/store/pix', 'GET', null, array(
                'orderId' => $order_id,
            ));

            return $this->_resultJsonFactory->create()->setData($response);
        } catch (\Exception $e) {
            $this->_getHelper()->log('Error ao buscar pix', $e->getMessage());
            return $this->_resultJsonFactory->create()->setData(array(
                "qrCode" => null,
                "qrCodeBase64" => null,
                "error" => $e->getMessage(),
            ));
        }
    }
}