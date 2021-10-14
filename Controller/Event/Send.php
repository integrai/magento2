<?php

namespace Integrai\Core\Controller\Event;

class Send extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    protected $_request;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;
    protected $_processEvent;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\ProcessEvent $processEvent
    )
    {
        $this->_request = $request;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_processEvent = $processEvent;

        return parent::__construct($context);
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    public function execute() {
        try{
            $data = json_decode($this->getRequest()->getContent(), true);
            $payload = $data['payload'];
            $this->_getHelper()->log('Executando evento', $data['event']);

            $response = $this->_processEvent->process($payload);

            return $this->_resultJsonFactory->create()->setData($response);
        } catch (\Throwable $e) {
            $this->_getHelper()->log('Erro ao executar o evento', $e->getMessage());

            return $this->_resultJsonFactory->create()->setData(array(
                'ok' => false,
                "error" => $e->getMessage()
            ));
        }
    }
}