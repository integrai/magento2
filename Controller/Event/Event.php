<?php

namespace Integrai\Core\Controller\Event;

class Event extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    protected $_request;
    protected $_resultJsonFactory;
    protected $_helper;
    protected $_api;
    protected $_connection;
    protected $_resource;
    protected $_processEventsFactory;
    protected $_processEvent;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Integrai\Core\Helper\Data $helper,
        \Integrai\Core\Model\Api $api,
        \Magento\Framework\App\ResourceConnection $resource,
        \Integrai\Core\Model\ProcessEventsFactory $processEventsFactory,
        \Integrai\Core\Model\ProcessEvent $processEvent
    )
    {
        $this->_request = $request;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_helper = $helper;
        $this->_api = $api;
        $this->_connection = $resource->getConnection();
        $this->_resource = $resource;
        $this->_processEventsFactory = $processEventsFactory;
        $this->_processEvent = $processEvent;

        return parent::__construct($context);
    }

    protected function _getHelper(){
        return $this->_helper;
    }

    protected function _getApi(){
        return $this->_api;
    }

    public function execute() {
        try{
            if (!$this->_helper->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
                return $this->_resultJsonFactory->create()
                    ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED)
                    ->setData(array("error" => "Unauthorized"));
            }

            $body = json_decode($this->getRequest()->getContent(), true);
            $eventId = $body['eventId'];
            $event = $body['event'];
            $payload = $body['payload'];
            $isSync = (bool)$body['isSync'];

            if ($isSync) {
                $this->_getHelper()->log('Executando evento', $event);

                $response = $this->_processEvent->process($payload);

                return $this->_resultJsonFactory->create()->setData($response);
            } else {
                $this->_getHelper()->log('Salvando o evento', $event);

                $this->_processEventsFactory->create()
                    ->setData(array(
                        'event_id' => $eventId,
                        'event' => $event,
                        'payload' => json_encode($payload),
                    ))
                    ->save();

                return $this->_resultJsonFactory->create()->setData(array(
                    'ok' => true
                ));
            }
        } catch (\Throwable $e) {
            $this->_getHelper()->log('Erro ao salvar o evento', $e->getMessage());
            return $this->_resultJsonFactory->create()
                ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR)
                ->setData(array(
                    'ok' => false,
                    "error" => $e->getMessage()
                ));
        }
    }
}